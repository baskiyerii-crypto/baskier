<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminApiManagementController extends Controller
{
    public function index()
    {
        return view('admin.api-management.index', [
            'iyzico_mode' => Setting::get('iyzico_mode', 'sandbox'),
            'iyzico_api_key' => Setting::get('iyzico_api_key', ''),
            'iyzico_secret_key' => Setting::get('iyzico_secret_key', ''),
            'iyzico_base_url' => Setting::get('iyzico_base_url', 'https://sandbox-api.iyzipay.com'),
            'basitkargo_api_key' => Setting::get('basitkargo_api_key', ''),
            'basitkargo_base_url' => Setting::get('basitkargo_base_url', ''),
            'openai_api_key' => Setting::get('openai_api_key', ''),
            'openai_model' => Setting::get('openai_model', 'gpt-4o-mini'),
            'evolution_base_url' => Setting::get('evolution_base_url', config('evolution.base_url')),
            'evolution_api_key' => Setting::get('evolution_api_key', config('evolution.api_key')),
            'evolution_instance' => Setting::get('evolution_instance', config('evolution.instance')),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
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

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) ($value ?? ''));
        }

        return back()->with('success', __('panel.settings_saved'));
    }
}
