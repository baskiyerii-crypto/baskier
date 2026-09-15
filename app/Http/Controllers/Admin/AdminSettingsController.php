<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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
        ]);
        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }
        return redirect()->route('admin.settings.index')->with('success', __('panel.settings_saved'));
    }
}
