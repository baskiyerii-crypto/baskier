<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\PlatformBranding;
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
            'ozalit_monthly_fee' => Setting::get('ozalit_monthly_fee', 199),
            'legal_company_title' => Setting::get('legal_company_title'),
            'legal_address' => Setting::get('legal_address'),
            'legal_tax_office' => Setting::get('legal_tax_office'),
            'legal_tax_number' => Setting::get('legal_tax_number'),
            'legal_mersis' => Setting::get('legal_mersis'),
            'legal_email' => Setting::get('legal_email'),
            'legal_phone' => Setting::get('legal_phone'),
            'legal_kep' => Setting::get('legal_kep'),
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
            'ozalit_monthly_fee' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'legal_company_title' => ['nullable', 'string', 'max:255'],
            'legal_address' => ['nullable', 'string', 'max:500'],
            'legal_tax_office' => ['nullable', 'string', 'max:120'],
            'legal_tax_number' => ['nullable', 'string', 'max:32'],
            'legal_mersis' => ['nullable', 'string', 'max:32'],
            'legal_email' => ['nullable', 'email', 'max:255'],
            'legal_phone' => ['nullable', 'string', 'max:32'],
            'legal_kep' => ['nullable', 'string', 'max:255'],
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
        ]);

        if ($request->hasFile('platform_logo')) {
            PlatformBranding::storeLogo($request->file('platform_logo'));
        }

        Setting::set('float_whatsapp_enabled', $request->boolean('float_whatsapp_enabled') ? '1' : '0');
        Setting::set('float_call_enabled', $request->boolean('float_call_enabled') ? '1' : '0');

        foreach ($validated as $key => $value) {
            if (in_array($key, ['platform_logo', 'float_whatsapp_enabled', 'float_call_enabled'], true)) {
                continue;
            }
            Setting::set($key, $value ?? '');
        }

        return redirect()->route('admin.settings.index')->with('success', __('panel.settings_saved'));
    }
}
