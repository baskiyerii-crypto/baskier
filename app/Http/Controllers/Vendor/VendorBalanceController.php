<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class VendorBalanceController extends Controller
{
    private function getVendor(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403, 'Satıcı hesabı bulunamadı.');
        }
        return $vendor;
    }

    public function index(Request $request)
    {
        $vendor = $this->getVendor($request);
        $transactions = $vendor->balanceTransactions()->latest()->paginate(20);
        $meetingFee = Setting::meetingFee();
        return view('vendor.balance.index', compact('vendor', 'transactions', 'meetingFee'));
    }

    public function topUp(Request $request)
    {
        $vendor = $this->getVendor($request);
        $validated = $request->validate(['amount' => ['required', 'numeric', 'min:10', 'max:10000']]);
        $amount = (float) $validated['amount'];
        $vendor->increment('balance', $amount);
        $vendor->balanceTransactions()->create([
            'amount' => $amount,
            'type' => 'topup',
            'description' => 'Bakiye yükleme (demo)',
            'balance_after' => $vendor->fresh()->balance,
        ]);
        return back()->with('success', '₺' . number_format($amount, 2, ',', '.') . ' bakiye yüklendi (demo).');
    }
}
