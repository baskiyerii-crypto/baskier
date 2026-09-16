<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminApiManagementController extends Controller
{
    private const API_KEYS = ['shopify', 'iyzico', 'basitkargo', 'openai', 'evolution'];

    public function index()
    {
        return view('admin.api-management.index', [
            'payment_provider' => Setting::get('payment_provider', 'shopify'),
            'shopify_enabled' => Setting::apiEnabled('shopify'),
            'shopify_shop_domain' => Setting::get('shopify_shop_domain', ''),
            'shopify_admin_token' => Setting::get('shopify_admin_token', ''),
            'shopify_api_version' => Setting::get('shopify_api_version', '2024-01'),
            'iyzico_enabled' => Setting::apiEnabled('iyzico'),
            'iyzico_mode' => Setting::get('iyzico_mode', 'sandbox'),
            'iyzico_api_key' => Setting::get('iyzico_api_key', ''),
            'iyzico_secret_key' => Setting::get('iyzico_secret_key', ''),
            'iyzico_base_url' => Setting::get('iyzico_base_url', 'https://sandbox-api.iyzipay.com'),
            'basitkargo_enabled' => Setting::apiEnabled('basitkargo'),
            'basitkargo_api_key' => Setting::get('basitkargo_api_key', ''),
            'basitkargo_base_url' => Setting::get('basitkargo_base_url', ''),
            'openai_enabled' => Setting::apiEnabled('openai'),
            'openai_api_key' => Setting::get('openai_api_key', ''),
            'openai_model' => Setting::get('openai_model', 'gpt-4o-mini'),
            'evolution_enabled' => Setting::apiEnabled('evolution'),
            'evolution_base_url' => Setting::get('evolution_base_url', config('evolution.base_url')),
            'evolution_api_key' => Setting::get('evolution_api_key', config('evolution.api_key')),
            'evolution_instance' => Setting::get('evolution_instance', config('evolution.instance')),
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
            'basitkargo_api_key' => ['nullable', 'string', 'max:255'],
            'basitkargo_base_url' => ['nullable', 'url', 'max:255'],
            'openai_api_key' => ['nullable', 'string', 'max:255'],
            'openai_model' => ['nullable', 'string', 'max:120'],
            'evolution_base_url' => ['nullable', 'string', 'max:255'],
            'evolution_api_key' => ['nullable', 'string', 'max:255'],
            'evolution_instance' => ['nullable', 'string', 'max:120'],
        ]);

        foreach (self::API_KEYS as $key) {
            Setting::set('api_'.$key.'_enabled', $request->boolean('api_'.$key.'_enabled') ? '1' : '0');
        }

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) ($value ?? ''));
        }

        return back()->with('success', __('panel.settings_saved'));
    }
}
