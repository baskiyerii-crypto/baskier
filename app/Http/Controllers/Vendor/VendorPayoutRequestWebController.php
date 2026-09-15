<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class VendorPayoutRequestWebController extends Controller
{
    private function vendor(Request $request)
    {
        $v = $request->user()->vendor;
        if (! $v) {
            abort(403, 'Satıcı hesabı bulunamadı.');
        }

        return $v;
    }

    public function index(Request $request)
    {
        $vendor = $this->vendor($request);
        $requests = PayoutRequest::where('vendor_id', $vendor->id)->latest()->paginate(15);

        $pendingTotal = (float) PayoutRequest::where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = max(0, (float) $vendor->balance - $pendingTotal);

        return view('vendor.payout-requests.index', compact('vendor', 'requests', 'pendingTotal', 'availableBalance'));
    }

    public function store(Request $request)
    {
        $vendor = $this->vendor($request);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10'],
            'iban' => ['required', 'string', 'regex:/^TR[0-9]{24}$/i'],
            'account_holder' => ['required', 'string', 'max:150'],
        ], [
            'iban.regex' => 'IBAN numarası TR ile başlamalı ve toplam 26 karakter (TR + 24 hane) olmalıdır.',
        ]);

        $amount = (float) $validated['amount'];

        // Bekleyen açık talepleri hesapla
        $pendingTotal = (float) PayoutRequest::where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = (float) $vendor->balance - $pendingTotal;

        if ($amount > $availableBalance) {
            return back()->with('error', 'Yetersiz çekilebilir bakiye. Açıkta bekleyen talepleriniz düşüldükten sonra çekebileceğiniz net tutar: ₺' . number_format(max(0, $availableBalance), 2, ',', '.'));
        }

        PayoutRequest::create([
            'vendor_id' => $vendor->id,
            'amount' => $amount,
            'status' => 'pending',
            'admin_note' => 'IBAN: ' . strtoupper($validated['iban']) . ' | Hesap Sahibi: ' . $validated['account_holder'],
        ]);

        return back()->with('success', 'Ödeme talebi başarıyla oluşturuldu. Finans birimi onayından sonra hesabınıza aktarılacaktır.');
    }
}
