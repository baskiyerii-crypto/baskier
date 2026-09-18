<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use RuntimeException;

class VendorPayoutRequestWebController extends Controller
{
    public function __construct(private PayoutService $payouts) {}

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
        $availableBalance = $this->payouts->availableBalance($vendor);
        $rulesText = $this->payouts->rulesText();
        $isPayoutDay = $this->payouts->isPayoutDay();
        $minAmount = $this->payouts->minAmount();
        $isOutdoorPanel = $request->routeIs('outdoor-panel.*');
        $layout = $isOutdoorPanel ? 'layouts.outdoor' : 'layouts.vendor';

        return view('vendor.payout-requests.index', compact(
            'vendor',
            'requests',
            'pendingTotal',
            'availableBalance',
            'rulesText',
            'isPayoutDay',
            'minAmount',
            'isOutdoorPanel',
            'layout'
        ));
    }

    public function store(Request $request)
    {
        $vendor = $this->vendor($request);
        $min = $this->payouts->minAmount();
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.$min],
            'iban' => ['required', 'string', 'regex:/^TR[0-9]{24}$/i'],
            'account_holder' => ['required', 'string', 'max:150'],
        ], [
            'iban.regex' => 'IBAN numarası TR ile başlamalı ve toplam 26 karakter (TR + 24 hane) olmalıdır.',
        ]);

        try {
            $this->payouts->request(
                $vendor,
                (float) $validated['amount'],
                $validated['iban'],
                $validated['account_holder']
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Ödeme talebi başarıyla oluşturuldu. Finans birimi onayından sonra hesabınıza aktarılacaktır.');
    }
}
