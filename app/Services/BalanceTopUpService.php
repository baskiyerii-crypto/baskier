<?php

namespace App\Services;

use App\Domain\PaymentStatus;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class BalanceTopUpService
{
    public function __construct(
        private IyzicoClient $iyzico,
        private ShopierClient $shopier
    ) {}

    /**
     * @return array{attempt: PaymentAttempt, redirect?: string, form?: array}
     */
    public function start(Vendor $vendor, User $user, float $amount, string $provider): array
    {
        if ($amount < 10 || $amount > 10000) {
            throw new RuntimeException('Yüklenecek tutar ₺10 – ₺10.000 aralığında olmalıdır.');
        }
        $provider = $provider === 'shopier' ? 'shopier' : 'iyzico';
        if ($provider === 'iyzico' && ! $this->iyzico->isConfigured()) {
            throw new RuntimeException('iyzico henüz yapılandırılmamış. Yönetici API ayarlarını kontrol edin.');
        }
        if ($provider === 'shopier' && ! $this->shopier->isConfigured()) {
            throw new RuntimeException('Shopier henüz yapılandırılmamış. Yönetici API ayarlarını kontrol edin.');
        }

        $attempt = PaymentAttempt::create([
            'conversation_id' => 'BY-TOPUP-'.Str::upper(Str::random(12)),
            'idempotency_key' => 'topup-'.$vendor->id.'-'.Str::uuid()->toString(),
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'provider' => $provider,
            'purpose' => 'balance_topup',
            'status' => PaymentStatus::PENDING,
            'amount' => $amount,
            'currency' => 'TRY',
            'order_ids' => [],
            'metadata' => ['purpose' => 'balance_topup', 'vendor_id' => $vendor->id],
        ]);

        if ($provider === 'shopier') {
            $form = $this->shopier->buildPaymentForm([
                'platform_order_id' => (string) $attempt->id,
                'product_name' => 'BaskiYeri bakiye yukleme',
                'total_order_value' => $amount,
                'currency' => 'TRY',
                'buyer_name' => explode(' ', $user->name)[0] ?? 'Satici',
                'buyer_surname' => count(explode(' ', trim($user->name))) > 1 ? implode(' ', array_slice(explode(' ', trim($user->name)), 1)) : 'Hesap',
                'buyer_email' => $user->email ?: ($vendor->email ?: 'satici@baskiyeri.com'),
                'buyer_id' => $user->id,
                'buyer_phone' => preg_replace('/\D+/', '', (string) ($vendor->phone ?: '5555555555')) ?: '5555555555',
                'callback' => route('payment.shopier.callback'),
            ]);
            $attempt->update([
                'status' => PaymentStatus::PROCESSING,
                'metadata' => array_merge((array) $attempt->metadata, ['shopier' => $form['fields']]),
            ]);

            return ['attempt' => $attempt, 'form' => $form];
        }

        $payload = $this->iyzicoPayload($attempt, $vendor, $user, $amount);
        $response = $this->iyzico->initializeCheckoutForm($payload);
        if (($response['status'] ?? '') !== 'success') {
            $attempt->update([
                'status' => PaymentStatus::FAILED,
                'error_message' => $response['errorMessage'] ?? 'Form başlatılamadı.',
            ]);
            throw new RuntimeException($response['errorMessage'] ?? 'iyzico ödeme formu başlatılamadı.');
        }
        $attempt->update([
            'status' => PaymentStatus::PROCESSING,
            'metadata' => array_merge((array) $attempt->metadata, [
                'token' => $response['token'] ?? null,
                'paymentPageUrl' => $response['paymentPageUrl'] ?? null,
            ]),
        ]);

        return [
            'attempt' => $attempt,
            'redirect' => $response['paymentPageUrl'] ?? null,
            'checkoutFormContent' => $response['checkoutFormContent'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $detail
     * @return array{success: bool, attempt: PaymentAttempt, errorMessage?: string}
     */
    public function finalizeIyzico(PaymentAttempt $attempt, array $detail): array
    {
        $status = $detail['status'] ?? 'failure';
        $paymentStatus = $detail['paymentStatus'] ?? 'FAILURE';
        if ($status === 'success' && $paymentStatus === 'SUCCESS') {
            $this->credit($attempt, (string) ($detail['paymentId'] ?? ''));

            return ['success' => true, 'attempt' => $attempt->fresh()];
        }

        $error = $detail['errorMessage'] ?? 'Ödeme onaylanmadı.';
        $attempt->update([
            'status' => PaymentStatus::FAILED,
            'error_code' => $detail['errorCode'] ?? 'PAYMENT_FAILED',
            'error_message' => $error,
        ]);

        return ['success' => false, 'attempt' => $attempt->fresh(), 'errorMessage' => $error];
    }

    public function finalizeShopier(PaymentAttempt $attempt, bool $paid, string $reference = ''): array
    {
        if (! $paid) {
            $attempt->update([
                'status' => PaymentStatus::FAILED,
                'error_message' => 'Shopier ödemesi onaylanmadı.',
            ]);

            return ['success' => false, 'attempt' => $attempt->fresh(), 'errorMessage' => 'Ödeme onaylanmadı.'];
        }
        $this->credit($attempt, $reference);

        return ['success' => true, 'attempt' => $attempt->fresh()];
    }

    public function credit(PaymentAttempt $attempt, string $reference = ''): void
    {
        DB::transaction(function () use ($attempt, $reference) {
            /** @var PaymentAttempt $locked */
            $locked = PaymentAttempt::query()->whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === PaymentStatus::PAID) {
                return;
            }
            $vendor = Vendor::query()->whereKey($locked->vendor_id)->lockForUpdate()->first();
            if (! $vendor) {
                throw new RuntimeException('Satıcı bulunamadı.');
            }
            $amount = (float) $locked->amount;
            $vendor->increment('balance', $amount);
            $vendor->refresh();
            $vendor->balanceTransactions()->create([
                'amount' => $amount,
                'type' => 'topup',
                'reference_type' => 'payment_attempt',
                'reference_id' => $locked->id,
                'description' => 'Bakiye yükleme ('.$locked->provider.')',
                'balance_after' => $vendor->balance,
            ]);
            $locked->update([
                'status' => PaymentStatus::PAID,
                'provider_reference' => $reference,
                'paid_at' => now(),
            ]);
        });
    }

    public function returnRoute(PaymentAttempt $attempt): string
    {
        $vendor = Vendor::query()->find($attempt->vendor_id);
        if ($vendor && $vendor->prefersOutdoorPanel() && \Illuminate\Support\Facades\Route::has('outdoor-panel.balance.index')) {
            return 'outdoor-panel.balance.index';
        }

        return 'vendor.balance.index';
    }

    /**
     * @return array<string, mixed>
     */
    private function iyzicoPayload(PaymentAttempt $attempt, Vendor $vendor, User $user, float $amount): array
    {
        $nameParts = explode(' ', trim($user->name));
        $formatted = number_format($amount, 2, '.', '');
        $address = $vendor->address ?: 'Turkiye';

        return [
            'locale' => 'tr',
            'conversationId' => $attempt->conversation_id,
            'price' => $formatted,
            'paidPrice' => $formatted,
            'currency' => 'TRY',
            'basketId' => 'BY-TOPUP-'.$attempt->id,
            'paymentGroup' => 'PRODUCT',
            'callbackUrl' => route('payment.iyzico.callback'),
            'enabledInstallments' => [1],
            'buyer' => [
                'id' => (string) $user->id,
                'name' => $nameParts[0] ?? 'Satici',
                'surname' => count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'Hesap',
                'email' => $user->email ?: 'satici@baskiyeri.com',
                'identityNumber' => '11111111111',
                'registrationAddress' => $address,
                'city' => $vendor->city ?: 'Istanbul',
                'country' => 'Turkey',
                'ip' => request()->ip() ?: '127.0.0.1',
            ],
            'shippingAddress' => [
                'contactName' => $user->name,
                'city' => $vendor->city ?: 'Istanbul',
                'country' => 'Turkey',
                'address' => $address,
            ],
            'billingAddress' => [
                'contactName' => $user->name,
                'city' => $vendor->city ?: 'Istanbul',
                'country' => 'Turkey',
                'address' => $address,
            ],
            'basketItems' => [[
                'id' => 'topup-'.$attempt->id,
                'name' => 'Bakiye yukleme',
                'category1' => 'Bakiye',
                'itemType' => 'VIRTUAL',
                'price' => $formatted,
            ]],
        ];
    }
}
