<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Domain\PaymentStatus;
use App\Exceptions\PaymentNotConfiguredException;
use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private IyzicoClient $iyzicoClient,
        private MarketplaceOrderService $orderService
    ) {}

    public function isIyzicoConfigured(): bool
    {
        return $this->iyzicoClient->isConfigured();
    }

    public function isShopifyConfigured(): bool
    {
        return config('marketplace.enable_shopify', false)
            && Setting::apiEnabled('shopify')
            && filled(Setting::get('shopify_shop_domain'))
            && filled(Setting::get('shopify_admin_token'));
    }

    public function provider(): string
    {
        $p = Setting::get('payment_provider', 'iyzico');
        if (! in_array($p, ['iyzico', 'shopify'], true)) {
            $p = 'iyzico';
        }

        return $p;
    }

    public function isConfigured(): bool
    {
        return $this->provider() === 'iyzico'
            ? $this->isIyzicoConfigured()
            : $this->isShopifyConfigured();
    }

    /**
     * Prepare an iyzico Checkout Form session for one or more orders under a single PaymentAttempt.
     *
     * @param list<Order> $orders
     */
    public function initializeIyzicoCheckout(
        PaymentAttempt $attempt,
        array $orders,
        User $user,
        Address $shippingAddress,
        Address $billingAddress
    ): array {
        if (! $this->isIyzicoConfigured()) {
            throw new PaymentNotConfiguredException('iyzico ödeme entegrasyonu yapılandırılmamıştır.');
        }

        $totalPrice = '0.00';
        $basketItems = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $itemTotal = bcmul((string) $item->price, (string) $item->quantity, 2);
                $totalPrice = bcadd($totalPrice, $itemTotal, 2);

                $basketItem = [
                    'id' => (string) $item->id,
                    'name' => mb_substr((string) $item->name, 0, 100),
                    'category1' => 'Baski',
                    'itemType' => ($item->product && $item->product->isDigital()) ? 'VIRTUAL' : 'PHYSICAL',
                    'price' => number_format((float) $itemTotal, 2, '.', ''),
                ];

                // Marketplace sub-merchant support if vendor has sub-merchant key
                $subKey = $order->vendor?->sub_merchant_key;
                if ($subKey) {
                    $basketItem['subMerchantKey'] = $subKey;
                    $basketItem['subMerchantPrice'] = number_format((float) $order->vendor_amount, 2, '.', '');
                }

                $basketItems[] = $basketItem;
            }
        }

        $formattedTotal = number_format((float) $totalPrice, 2, '.', '');

        $nameParts = explode(' ', trim($user->name));
        $buyerName = $nameParts[0] ?? 'Musteri';
        $buyerSurname = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'Kullanici';

        $payload = [
            'locale' => 'tr',
            'conversationId' => $attempt->conversation_id,
            'price' => $formattedTotal,
            'paidPrice' => $formattedTotal,
            'currency' => 'TRY',
            'basketId' => 'BY-BASKET-' . $attempt->id,
            'paymentGroup' => 'PRODUCT',
            'callbackUrl' => route('payment.iyzico.callback'),
            'enabledInstallments' => [1, 2, 3, 6, 9, 12],
            'buyer' => [
                'id' => (string) $user->id,
                'name' => $buyerName,
                'surname' => $buyerSurname,
                'email' => $user->email ?: 'musteri@baskiyeri.com',
                'identityNumber' => $user->identity_number ?: '11111111111',
                'registrationAddress' => $shippingAddress->formatted,
                'city' => $shippingAddress->city ?: 'Istanbul',
                'country' => 'Turkey',
                'ip' => request()->ip() ?: '127.0.0.1',
            ],
            'shippingAddress' => [
                'contactName' => $shippingAddress->full_name ?: $user->name,
                'city' => $shippingAddress->city ?: 'Istanbul',
                'country' => 'Turkey',
                'address' => $shippingAddress->formatted,
            ],
            'billingAddress' => [
                'contactName' => $billingAddress->full_name ?: $user->name,
                'city' => $billingAddress->city ?: 'Istanbul',
                'country' => 'Turkey',
                'address' => $billingAddress->formatted,
            ],
            'basketItems' => $basketItems,
        ];

        $response = $this->iyzicoClient->initializeCheckoutForm($payload);

        $status = $response['status'] ?? 'failure';
        if ($status !== 'success') {
            $errorMsg = $response['errorMessage'] ?? 'Ödeme formu başlatılamadı.';
            $attempt->update([
                'status' => PaymentStatus::FAILED,
                'error_code' => $response['errorCode'] ?? 'INIT_FAIL',
                'error_message' => $errorMsg,
            ]);

            throw new \RuntimeException($errorMsg);
        }

        $attempt->update([
            'status' => PaymentStatus::PROCESSING,
            'metadata' => [
                'token' => $response['token'] ?? null,
                'paymentPageUrl' => $response['paymentPageUrl'] ?? null,
            ],
        ]);

        return [
            'token' => $response['token'] ?? null,
            'checkoutFormContent' => $response['checkoutFormContent'] ?? null,
            'paymentPageUrl' => $response['paymentPageUrl'] ?? null,
        ];
    }

    /**
     * Handle and verify server-to-server callback from iyzico.
     */
    public function verifyAndProcessIyzicoCallback(string $token): array
    {
        $attempt = PaymentAttempt::where('metadata->token', $token)->first();
        if (! $attempt) {
            Log::warning('iyzico_callback_attempt_not_found', ['token' => $token]);
            throw new \InvalidArgumentException('Geçersiz veya süresi dolmuş ödeme oturumu.');
        }

        $detail = $this->iyzicoClient->getCheckoutFormDetail($token, $attempt->conversation_id);

        $status = $detail['status'] ?? 'failure';
        $paymentStatus = $detail['paymentStatus'] ?? 'FAILURE';

        if ($status === 'success' && $paymentStatus === 'SUCCESS') {
            // Validate currency and amount
            $paidCurrency = $detail['currency'] ?? 'TRY';
            $paidPrice = $detail['paidPrice'] ?? '0.00';

            if ($paidCurrency !== 'TRY') {
                Log::error('iyzico_currency_mismatch', ['expected' => 'TRY', 'actual' => $paidCurrency]);
                throw new \RuntimeException('Geçersiz para birimi.');
            }

            $attempt->update([
                'status' => PaymentStatus::PAID,
                'provider_reference' => (string) ($detail['paymentId'] ?? ''),
                'paid_at' => now(),
                'metadata' => array_merge((array) $attempt->metadata, ['detail' => $detail]),
            ]);

            // Confirm all orders under this payment attempt
            $orderIds = (array) ($attempt->order_ids ?? []);
            $confirmedOrders = [];
            foreach ($orderIds as $orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $confirmedOrders[] = $this->orderService->confirmPaidOrder($order, $attempt, $detail);
                }
            }

            return [
                'success' => true,
                'attempt' => $attempt,
                'orders' => $confirmedOrders,
            ];
        }

        // Payment failed or cancelled
        $errorMessage = $detail['errorMessage'] ?? 'Ödeme işlemi onaylanmadı.';
        $attempt->update([
            'status' => PaymentStatus::FAILED,
            'error_code' => $detail['errorCode'] ?? 'PAYMENT_FAILED',
            'error_message' => $errorMessage,
        ]);

        $orderIds = (array) ($attempt->order_ids ?? []);
        foreach ($orderIds as $orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $this->orderService->failOrder($order, $errorMessage);
            }
        }

        return [
            'success' => false,
            'attempt' => $attempt,
            'errorMessage' => $errorMessage,
        ];
    }
}
