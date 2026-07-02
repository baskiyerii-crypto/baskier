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

        return view('vendor.payout-requests.index', compact('vendor', 'requests'));
    }

    public function store(Request $request)
    {
        $vendor = $this->vendor($request);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);
        $amount = (float) $validated['amount'];
        if ($amount > (float) $vendor->balance) {
            return back()->with('error', 'Tutar mevcut bakiyeden büyük olamaz.');
        }

        PayoutRequest::create([
            'vendor_id' => $vendor->id,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Ödeme talebi oluşturuldu. Yönetici onayından sonra işleme alınır.');
    }
}
