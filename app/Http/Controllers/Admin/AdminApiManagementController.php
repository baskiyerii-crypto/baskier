<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\IyzicoClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminApiManagementController extends Controller
{
    private const API_KEYS = ['shopify', 'iyzico', 'shopier', 'basitkargo', 'openai', 'evolution', 'google'];

    private const SECRET_KEYS = [
        'iyzico_secret_key',
        'shopier_api_secret',
        'shopify_admin_token',
        'basitkargo_api_key',
        'openai_api_key',
        'evolution_api_key',
        'google_maps_api_key',
    ];

    public function index()
    {
        return view('admin.api-management.index', [
            'payment_provider' => Setting::get('payment_provider', 'iyzico'),
            'shopify_enabled' => Setting::apiEnabled('shopify'),
            'shopify_shop_domain' => Setting::get('shopify_shop_domain', ''),
            'shopify_admin_token' => $this->mask(Setting::get('shopify_admin_token', '')),
            'shopify_api_version' => Setting::get('shopify_api_version', '2024-01'),
            'iyzico_enabled' => Setting::apiEnabled('iyzico'),
            'iyzico_mode' => Setting::get('iyzico_mode', 'sandbox'),
            'iyzico_api_key' => Setting::get('iyzico_api_key', ''),
            'iyzico_secret_key' => $this->mask(Setting::get('iyzico_secret_key', '')),
            'iyzico_base_url' => Setting::get('iyzico_base_url', 'https://sandbox-api.iyzipay.com'),
            'iyzico_last_test' => Setting::get('iyzico_last_test_result'),
            'shopier_enabled' => Setting::apiEnabled('shopier', false),
            'shopier_mode' => Setting::get('shopier_mode', 'test'),
            'shopier_api_key' => Setting::get('shopier_api_key', ''),
            'shopier_api_secret' => $this->mask(Setting::get('shopier_api_secret', '')),
            'shopier_website_index' => Setting::get('shopier_website_index', '1'),
            'shopier_last_test' => Setting::get('shopier_last_test_result'),
            'basitkargo_enabled' => Setting::apiEnabled('basitkargo'),
            'basitkargo_api_key' => $this->mask(Setting::get('basitkargo_api_key', '')),
            'basitkargo_base_url' => Setting::get('basitkargo_base_url', ''),
            'openai_enabled' => Setting::apiEnabled('openai'),
            'openai_api_key' => $this->mask(Setting::get('openai_api_key', '')),
            'openai_model' => Setting::get('openai_model', 'gpt-4o-mini'),
            'evolution_enabled' => Setting::apiEnabled('evolution'),
            'evolution_base_url' => Setting::get('evolution_base_url', config('evolution.base_url')),
            'evolution_api_key' => $this->mask(Setting::get('evolution_api_key', config('evolution.api_key'))),
            'evolution_instance' => Setting::get('evolution_instance', config('evolution.instance')),
            'google_enabled' => Setting::apiEnabled('google', false),
            'google_maps_api_key' => $this->mask(Setting::get('google_maps_api_key', '')),
            'google_last_test' => Setting::get('google_last_test_result'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'payment_provider' => ['required', 'in:shopify,iyzico'],
            'shopify_shop_domain' => ['nullable', 'string', 'max:255'],
            'shopify_admin_token' => ['nullable', 'string', 'max:512'],
            'shopify_api_version' => ['nullable', 'string', 'max:32'],
            'iyzico_mode' => ['required', 'in:sandbox,live'],
            'iyzico_api_key' => ['nullable', 'string', 'max:255'],
            'iyzico_secret_key' => ['nullable', 'string', 'max:255'],
            'iyzico_base_url' => ['nullable', 'url', 'max:255'],
            'shopier_mode' => ['nullable', 'in:test,live'],
            'shopier_api_key' => ['nullable', 'string', 'max:255'],
            'shopier_api_secret' => ['nullable', 'string', 'max:255'],
            'shopier_website_index' => ['nullable', 'integer', 'min:1', 'max:20'],
            'basitkargo_api_key' => ['nullable', 'string', 'max:255'],
            'basitkargo_base_url' => ['nullable', 'url', 'max:255'],
            'openai_api_key' => ['nullable', 'string', 'max:255'],
            'openai_model' => ['nullable', 'string', 'max:120'],
            'evolution_base_url' => ['nullable', 'string', 'max:255'],
            'evolution_api_key' => ['nullable', 'string', 'max:255'],
            'evolution_instance' => ['nullable', 'string', 'max:120'],
            'google_maps_api_key' => ['nullable', 'string', 'max:255'],
        ]);

        foreach (self::API_KEYS as $key) {
            Setting::set('api_'.$key.'_enabled', $request->boolean('api_'.$key.'_enabled') ? '1' : '0');
        }

        foreach ($validated as $key => $value) {
            if (in_array($key, self::SECRET_KEYS, true)) {
                // If value is masked with bullets, skip overwriting
                if (empty($value) || str_starts_with((string) $value, '••••')) {
                    continue;
                }
                Setting::setSecret($key, (string) $value);
            } else {
                Setting::set($key, (string) ($value ?? ''));
            }
        }

        return back()->with('success', __('panel.settings_saved'));
    }

    public function testIyzico(Request $request, IyzicoClient $client)
    {
        if (! $client->isConfigured()) {
            return back()->with('error', 'iyzico API anahtarları henüz girilmemiş.');
        }

        try {
            $headers = $client->generateV2Headers('/payment/test', '{}');
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($client->baseUrl() . '/payment/test');

            $status = $response->status();
            $ok = $response->successful();

            Setting::set('iyzico_last_test_result', json_encode([
                'timestamp' => now()->toIso8601String(),
                'status' => $ok ? 'success' : 'failed',
                'http_code' => $status,
            ]));

            if ($ok) {
                return back()->with('success', "iyzico {$client->mode()} bağlantısı başarılı! (HTTP {$status})");
            }

            return back()->with('error', "iyzico yanıt verdi ancak hata kodu döndü: HTTP {$status}");
        } catch (\Throwable $e) {
            return back()->with('error', 'iyzico sunucusuna erişilemedi: ' . $e->getMessage());
        }
    }

    public function testShopier(\App\Services\ShopierClient $client)
    {
        if (! $client->isConfigured()) {
            return back()->with('error', 'Shopier API anahtarları henüz girilmemiş.');
        }
        $ok = $client->ping();
        Setting::set('shopier_last_test_result', json_encode([
            'timestamp' => now()->toIso8601String(),
            'status' => $ok ? 'success' : 'failed',
            'mode' => $client->mode(),
        ]));

        return $ok
            ? back()->with('success', 'Shopier '.$client->mode().' uç noktasına erişildi.')
            : back()->with('error', 'Shopier uç noktasına ulaşılamadı.');
    }

    public function testGoogle()
    {
        if (! Setting::apiEnabled('google', false)) {
            return back()->with('error', 'Google Maps API kapalı.');
        }
        $key = Setting::get('google_maps_api_key', '');
        if ($key === '') {
            return back()->with('error', 'Google Maps API anahtarı girilmemiş.');
        }

        try {
            $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/streetview/metadata', [
                'location' => '41.0082,28.9784',
                'key' => $key,
            ]);
            $status = $response->json('status');
            $ok = $response->successful() && in_array($status, ['OK', 'ZERO_RESULTS'], true);
            Setting::set('google_last_test_result', json_encode([
                'timestamp' => now()->toIso8601String(),
                'status' => $ok ? 'success' : 'failed',
                'google_status' => $status,
                'http_code' => $response->status(),
            ]));

            return $ok
                ? back()->with('success', 'Google Street View Metadata yanıt verdi ('.$status.').')
                : back()->with('error', 'Google yanıtı geçersiz: '.($status ?: 'HTTP '.$response->status()));
        } catch (\Throwable $e) {
            return back()->with('error', 'Google sunucusuna erişilemedi: '.$e->getMessage());
        }
    }

    private function mask(?string $val): string
    {
        if (empty($val)) {
            return '';
        }
        $len = strlen($val);
        if ($len <= 4) {
            return '••••';
        }

        return '••••••••' . substr($val, -4);
    }
}
