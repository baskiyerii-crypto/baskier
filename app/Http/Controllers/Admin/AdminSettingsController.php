<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\PlatformBranding;
use App\Support\SiteMenu;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'commission_rate' => Setting::get('commission_rate', 10),
            'payout_day_of_month' => Setting::get('payout_day_of_month', 5),
            'commission_wait_days' => Setting::get('commission_wait_days', 15),
            'contract_acceptance_days' => Setting::get('contract_acceptance_days', 15),
            'meeting_fee' => Setting::get('meeting_fee', 50),
            'freelancer_monthly_fee' => Setting::get('freelancer_monthly_fee', 299),
            'quotes_monthly_fee' => Setting::get('quotes_monthly_fee', 199),
            'platform_logo' => Setting::get('platform_logo'),
            'platform_name' => Setting::get('platform_name', 'BaskıYeri'),
            'platform_address' => Setting::get('platform_address'),
            'platform_phone' => Setting::get('platform_phone'),
            'platform_email' => Setting::get('platform_email'),
            'platform_map_embed_url' => Setting::get('platform_map_embed_url'),
            'platform_map_lat' => Setting::get('platform_map_lat'),
            'platform_map_lng' => Setting::get('platform_map_lng'),
            'platform_social_instagram' => Setting::get('platform_social_instagram'),
            'platform_social_website' => Setting::get('platform_social_website'),
            'whatsapp_number' => Setting::get('whatsapp_number'),
            'call_number' => Setting::get('call_number'),
            'float_whatsapp_enabled' => Setting::get('float_whatsapp_enabled', '1'),
            'float_call_enabled' => Setting::get('float_call_enabled', '1'),
            'menu_items_json' => Setting::get('menu_items_json', json_encode(SiteMenu::defaults(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'payout_day_of_month' => ['required', 'integer', 'min:1', 'max:28'],
            'commission_wait_days' => ['required', 'integer', 'min:0', 'max:90'],
            'contract_acceptance_days' => ['required', 'integer', 'min:1', 'max:90'],
            'meeting_fee' => ['required', 'numeric', 'min:0', 'max:1000'],
            'freelancer_monthly_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'quotes_monthly_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'platform_logo' => ['nullable', 'image', 'max:2048'],
            'platform_name' => ['required', 'string', 'max:80'],
            'platform_address' => ['nullable', 'string', 'max:500'],
            'platform_phone' => ['nullable', 'string', 'max:32'],
            'platform_email' => ['nullable', 'email', 'max:255'],
            'platform_map_embed_url' => ['nullable', 'string', 'max:1000'],
            'platform_map_lat' => ['nullable', 'numeric'],
            'platform_map_lng' => ['nullable', 'numeric'],
            'platform_social_instagram' => ['nullable', 'string', 'max:255'],
            'platform_social_website' => ['nullable', 'url', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:32'],
            'call_number' => ['nullable', 'string', 'max:32'],
            'float_whatsapp_enabled' => ['nullable', 'boolean'],
            'float_call_enabled' => ['nullable', 'boolean'],
            'menu_items_json' => ['nullable', 'string', 'max:20000'],
        ]);

        if ($request->hasFile('platform_logo')) {
            PlatformBranding::storeLogo($request->file('platform_logo'));
        }

        if (! empty($validated['menu_items_json'])) {
            $decoded = json_decode($validated['menu_items_json'], true);
            if (! is_array($decoded)) {
                return back()->withInput()->with('error', 'Menü JSON geçersiz.');
            }
            Setting::set('menu_items_json', json_encode(array_values($decoded), JSON_UNESCAPED_UNICODE));
        }

        Setting::set('float_whatsapp_enabled', $request->boolean('float_whatsapp_enabled') ? '1' : '0');
        Setting::set('float_call_enabled', $request->boolean('float_call_enabled') ? '1' : '0');

        foreach ($validated as $key => $value) {
            if (in_array($key, ['platform_logo', 'menu_items_json', 'float_whatsapp_enabled', 'float_call_enabled'], true)) {
                continue;
            }
            Setting::set($key, $value ?? '');
        }

        return redirect()->route('admin.settings.index')->with('success', __('panel.settings_saved'));
    }
}
