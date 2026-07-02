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
            'meeting_fee' => Setting::get('meeting_fee', 50),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'payout_day_of_month' => ['required', 'integer', 'min:1', 'max:28'],
            'commission_wait_days' => ['required', 'integer', 'min:0', 'max:90'],
            'meeting_fee' => ['required', 'numeric', 'min:0', 'max:1000'],
        ]);
        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }
        return redirect()->route('admin.settings.index')->with('success', 'Ayarlar güncellendi.');
    }
}
