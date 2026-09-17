<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IyzicoClient
{
    public function isConfigured(): bool
    {
        return Setting::apiEnabled('iyzico')
            && filled(Setting::get('iyzico_api_key'))
            && filled(Setting::get('iyzico_secret_key'));
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

    /**
     * Generate standard IYZWSv2 HMAC-SHA256 authorization headers.
     */
    public function generateV2Headers(string $uriPath, string $requestBody): array
    {
        $apiKey = (string) Setting::get('iyzico_api_key', '');
        $secretKey = (string) Setting::get('iyzico_secret_key', '');
        $randomKey = Str::random(12) . microtime(true);

        $payloadToSign = $randomKey . $uriPath . $requestBody;
        $signature = hash_hmac('sha256', $payloadToSign, $secretKey);

        return [
            'Authorization' => 'IYZWSv2 ' . $apiKey . ':' . $signature,
            'x-iyzi-rnd' => $randomKey,
            'x-iyzi-client-version' => 'iyzico-laravel-v2',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Initialize Checkout Form for 3D Secure / Hosted Payment.
     */
    public function initializeCheckoutForm(array $params): array
    {
        $uriPath = '/payment/iyzipay/checkoutform/initialize/auth/ecom';
        $bodyJson = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $headers = $this->generateV2Headers($uriPath, $bodyJson);

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($this->baseUrl() . $uriPath, $params);

        $body = $response->json() ?? [];

        Log::info('iyzico_checkout_form_initialized', [
            'status' => $body['status'] ?? 'unknown',
            'conversationId' => $params['conversationId'] ?? null,
            'paymentPageUrl' => $body['paymentPageUrl'] ?? null,
        ]);

        return $body;
    }

    /**
     * Query / Retrieve Checkout Form detail from iyzico server-to-server.
     */
    public function getCheckoutFormDetail(string $token, string $conversationId): array
    {
        $uriPath = '/payment/iyzipay/checkoutform/auth/ecom/detail';
        $params = [
            'locale' => 'tr',
            'conversationId' => $conversationId,
            'token' => $token,
        ];
        $bodyJson = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $headers = $this->generateV2Headers($uriPath, $bodyJson);

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($this->baseUrl() . $uriPath, $params);

        return $response->json() ?? [];
    }

    /**
     * Verify X-IYZ-SIGNATURE-V3 header on incoming webhooks.
     */
    public function verifyWebhookSignature(?string $signatureHeader, string $rawBody): bool
    {
        if (empty($signatureHeader)) {
            return false;
        }

        $secretKey = (string) (Setting::get('iyzico_webhook_secret') ?: Setting::get('iyzico_secret_key', ''));
        if (empty($secretKey)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $rawBody, $secretKey);

        return hash_equals($expectedSignature, $signatureHeader);
    }
}
