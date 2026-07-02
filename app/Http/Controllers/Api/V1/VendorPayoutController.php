<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class VendorPayoutController extends ApiController
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            return $this->fail('Satıcı hesabı yok.', null, 403);
        }
        $rows = PayoutRequest::where('vendor_id', $vendor->id)->latest()->paginate(20);

        return $this->ok($rows);
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            return $this->fail('Satıcı hesabı yok.', null, 403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);
        $amount = (float) $validated['amount'];
        if ($amount > (float) $vendor->balance) {
            return $this->fail('Tutar bakiyeden büyük olamaz.', null, 422);
        }

        $row = PayoutRequest::create([
            'vendor_id' => $vendor->id,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        return $this->ok($row, 'Ödeme talebi oluşturuldu.', null, 201);
    }
}
