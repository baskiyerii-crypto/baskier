<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function provider(): string
    {
        $p = Setting::get('payment_provider', 'shopify');

        return in_array($p, ['shopify', 'iyzico'], true) ? $p : 'shopify';
    }

    public function isShopifyConfigured(): bool
    {
        return filled(Setting::get('shopify_shop_domain')) && filled(Setting::get('shopify_admin_token'));
    }

    public function isIyzicoConfigured(): bool
    {
        return filled(Setting::get('iyzico_api_key')) && filled(Setting::get('iyzico_secret_key'));
    }

    public function isConfigured(): bool
    {
        return $this->provider() === 'shopify'
            ? $this->isShopifyConfigured()
            : $this->isIyzicoConfigured();
    }

    public function mode(): string
    {
        return Setting::get('iyzico_mode', 'sandbox') === 'live' ? 'live' : 'sandbox';
    }

    public function baseUrl(): string
    {
        $custom = Setting::get('iyzico_base_url');
        if ($custom) {
            return rtrim($custom, '/');
        }

        return $this->mode() === 'live'
            ? 'https://api.iyzipay.com'
            : 'https://sandbox-api.iyzipay.com';
    }

    public function recordDemoPayment(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'provider' => 'demo',
            'reference' => 'DEMO-'.$order->order_number,
            'amount' => $order->subtotal,
            'status' => 'completed',
            'meta' => ['note' => 'Demo payment'],
        ]);
    }

    /**
     * Create Shopify draft order and return invoice/checkout URL.
     * Falls back to demo paid + null URL when Shopify is not configured.
     */
    public function createShopifyCheckout(Order $order): array
    {
        if (! $this->isShopifyConfigured()) {
            $payment = $this->recordDemoPayment($order);
            $order->update(['payment_status' => 'paid']);

            return ['url' => null, 'payment' => $payment, 'demo' => true];
        }

        $shop = preg_replace('#^https?://#', '', rtrim((string) Setting::get('shopify_shop_domain'), '/'));
        $token = Setting::get('shopify_admin_token');
        $apiVersion = Setting::get('shopify_api_version', '2024-01');

        $payload = [
            'draft_order' => [
                'line_items' => [[
                    'title' => 'Siparis '.$order->order_number,
                    'quantity' => 1,
                    'price' => number_format((float) $order->subtotal, 2, '.', ''),
                ]],
                'note' => 'BaskiYeri order #'.$order->order_number,
                'email' => $order->invoice_email,
                'tags' => 'baskiyeri,order-'.$order->id,
                'use_customer_default_address' => true,
            ],
        ];

        try {
            $res = Http::withHeaders([
                'X-Shopify-Access-Token' => $token,
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post("https://{$shop}/admin/api/{$apiVersion}/draft_orders.json", $payload);

            $body = $res->json() ?? [];
            $draft = $body['draft_order'] ?? [];
            $invoiceUrl = $draft['invoice_url'] ?? null;

            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'shopify',
                'reference' => (string) ($draft['id'] ?? ('SH-'.$order->order_number)),
                'amount' => $order->subtotal,
                'status' => $invoiceUrl ? 'pending' : 'failed',
                'meta' => $body,
            ]);

            $order->update([
                'payment_status' => $invoiceUrl ? 'pending' : 'failed',
            ]);

            if (! $invoiceUrl) {
                throw new \RuntimeException($body['errors'] ?? 'Shopify ödeme bağlantısı oluşturulamadı.');
            }

            return ['url' => $invoiceUrl, 'payment' => $payment, 'demo' => false];
        } catch (\Throwable $e) {
            Log::warning('shopify_checkout_failed', ['order' => $order->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function markShopifyPaid(Order $order, ?string $reference = null): Payment
    {
        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('provider', 'shopify')
            ->latest()
            ->first();

        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'reference' => $reference ?: $payment->reference,
            ]);
        } else {
            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'shopify',
                'reference' => $reference ?: ('SH-DONE-'.$order->order_number),
                'amount' => $order->subtotal,
                'status' => 'completed',
                'meta' => [],
            ]);
        }

        $order->update(['payment_status' => 'paid']);

        return $payment;
    }

    public function chargeCard(Order $order, array $card): Payment
    {
        if ($this->provider() === 'shopify') {
            throw new \RuntimeException('Aktif ödeme sağlayıcısı Shopify. Kart ile iyzico için API yönetiminden geçiş yapın.');
        }

        if (! $this->isIyzicoConfigured()) {
            $payment = $this->recordDemoPayment($order);
            $order->update(['payment_status' => 'paid']);

            return $payment;
        }

        $conversationId = 'BY-'.$order->order_number.'-'.Str::random(6);
        $payload = [
            'locale' => 'tr',
            'conversationId' => $conversationId,
            'price' => number_format((float) $order->subtotal, 2, '.', ''),
            'paidPrice' => number_format((float) $order->subtotal, 2, '.', ''),
            'currency' => 'TRY',
            'installment' => 1,
            'paymentChannel' => 'WEB',
            'paymentGroup' => 'PRODUCT',
            'paymentCard' => [
                'cardHolderName' => $card['card_holder_name'] ?? '',
                'cardNumber' => preg_replace('/\D/', '', $card['card_number'] ?? ''),
                'expireMonth' => substr((string) ($card['card_expiry'] ?? ''), 0, 2),
                'expireYear' => '20'.substr((string) ($card['card_expiry'] ?? ''), -2),
                'cvc' => $card['card_cvc'] ?? '',
            ],
            'buyer' => [
                'id' => (string) $order->user_id,
                'name' => $order->invoice_full_name ?: 'Musteri',
                'surname' => 'BY',
                'email' => $order->invoice_email ?: 'info@baskiyeri.com',
                'identityNumber' => $order->invoice_identity_number ?: '11111111111',
                'registrationAddress' => $order->shipping_address ?: 'Adres',
                'city' => 'Istanbul',
                'country' => 'Turkey',
            ],
            'billingAddress' => [
                'contactName' => $order->invoice_full_name ?: 'Musteri',
                'city' => 'Istanbul',
                'country' => 'Turkey',
                'address' => $order->shipping_address ?: 'Adres',
            ],
            'shippingAddress' => [
                'contactName' => $order->invoice_full_name ?: 'Musteri',
                'city' => 'Istanbul',
                'country' => 'Turkey',
                'address' => $order->shipping_address ?: 'Adres',
            ],
            'basketItems' => [[
                'id' => (string) $order->id,
                'name' => 'Siparis '.$order->order_number,
                'category1' => 'Genel',
                'itemType' => 'PHYSICAL',
                'price' => number_format((float) $order->subtotal, 2, '.', ''),
            ]],
        ];

        try {
            $response = Http::withHeaders($this->authHeaders(json_encode($payload)))
                ->timeout(30)
                ->post($this->baseUrl().'/payment/auth', $payload);

            $body = $response->json() ?? [];
            $ok = ($body['status'] ?? '') === 'success';

            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'iyzico',
                'reference' => $body['paymentId'] ?? $conversationId,
                'amount' => $order->subtotal,
                'status' => $ok ? 'completed' : 'failed',
                'meta' => $body,
            ]);

            $order->update(['payment_status' => $ok ? 'paid' : 'failed']);

            if (! $ok) {
                throw new \RuntimeException($body['errorMessage'] ?? 'iyzico ödeme başarısız.');
            }

            return $payment;
        } catch (\Throwable $e) {
            Log::warning('iyzico_charge_failed', ['order' => $order->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function authHeaders(string $requestBody): array
    {
        $apiKey = Setting::get('iyzico_api_key');
        $secret = Setting::get('iyzico_secret_key');
        $random = Str::random(8).microtime(true);
        $hashStr = $apiKey.$random.$secret.$requestBody;
        $hash = base64_encode(sha1($hashStr, true));

        return [
            'Authorization' => 'IYZWS '.$apiKey.':'.$hash,
            'x-iyzi-rnd' => $random,
            'Content-Type' => 'application/json',
        ];
    }
}
