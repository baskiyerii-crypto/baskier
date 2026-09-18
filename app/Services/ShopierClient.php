<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopierClient
{
    public function isConfigured(): bool
    {
        return Setting::apiEnabled('shopier', false)
            && filled(Setting::get('shopier_api_key'))
            && filled(Setting::get('shopier_api_secret'));
    }

    public function mode(): string
    {
        return Setting::get('shopier_mode', 'test') === 'live' ? 'live' : 'test';
    }

    public function paymentUrl(): string
    {
        $custom = Setting::get('shopier_payment_url');
        if ($custom) {
            return rtrim($custom, '/');
        }

        return 'https://www.shopier.com/ShowProduct/api_pay4.php';
    }

    public function signature(string $randomNr, string $orderId, string $total, string $currency): string
    {
        $secret = (string) Setting::get('shopier_api_secret', '');
        $payload = $randomNr.$orderId.$total.$currency;

        return base64_encode(hash_hmac('sha256', $payload, $secret, true));
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array{url: string, fields: array<string, mixed>}
     */
    public function buildPaymentForm(array $fields): array
    {
        $random = Str::random(20);
        $orderId = (string) $fields['platform_order_id'];
        $total = number_format((float) $fields['total_order_value'], 2, '.', '');
        $currency = (string) ($fields['currency'] ?? 'TRY');

        $payload = [
            'API_key' => (string) Setting::get('shopier_api_key', ''),
            'website_index' => (int) Setting::get('shopier_website_index', 1),
            'platform_order_id' => $orderId,
            'product_name' => $fields['product_name'] ?? 'Bakiye yukleme',
            'product_type' => 1,
            'buyer_name' => $fields['buyer_name'] ?? 'Alici',
            'buyer_surname' => $fields['buyer_surname'] ?? 'Hesap',
            'buyer_email' => $fields['buyer_email'] ?? 'destek@baskiyeri.com',
            'buyer_account_age' => 0,
            'buyer_id_nr' => $fields['buyer_id'] ?? 0,
            'buyer_phone' => $fields['buyer_phone'] ?? '5555555555',
            'billing_address' => $fields['address'] ?? 'Turkiye',
            'billing_city' => $fields['city'] ?? 'Istanbul',
            'billing_country' => 'TR',
            'billing_postcode' => '34000',
            'shipping_address' => $fields['address'] ?? 'Turkiye',
            'shipping_city' => $fields['city'] ?? 'Istanbul',
            'shipping_country' => 'TR',
            'shipping_postcode' => '34000',
            'total_order_value' => $total,
            'currency' => $currency,
            'platform' => 0,
            'is_in_frame' => 0,
            'current_language' => 0,
            'modul_version' => '1.0.4',
            'random_nr' => $random,
            'signature' => $this->signature($random, $orderId, $total, $currency),
            'callback' => $fields['callback'] ?? route('payment.shopier.callback'),
        ];

        Log::info('shopier_form_built', [
            'order_id' => $orderId,
            'mode' => $this->mode(),
        ]);

        return [
            'url' => $this->paymentUrl(),
            'fields' => $payload,
        ];
    }

    public function verifyCallback(array $payload): bool
    {
        $orderId = (string) ($payload['platform_order_id'] ?? '');
        $status = (string) ($payload['status'] ?? '');
        $random = (string) ($payload['random_nr'] ?? '');
        $incoming = (string) ($payload['signature'] ?? '');
        $secret = (string) Setting::get('shopier_api_secret', '');
        $expected = base64_encode(hash_hmac('sha256', $random.$orderId.$status, $secret, true));

        return hash_equals($expected, $incoming);
    }

    public function ping(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }
        try {
            $response = Http::timeout(8)->head($this->paymentUrl());

            return $response->successful() || $response->status() < 500;
        } catch (\Throwable) {
            return false;
        }
    }
}
