<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PayoutRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPayoutRequestController extends ApiController
{
    public function index(Request $request)
    {
        $q = PayoutRequest::with('vendor')->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        return $this->ok($q->paginate(30));
    }

    public function approve(Request $request, PayoutRequest $payoutRequest)
    {
        if ($payoutRequest->status !== 'pending') {
            return $this->fail('Bu talep zaten işlendi.', null, 422);
        }

        try {
            DB::transaction(function () use ($payoutRequest, $request) {
                /** @var Vendor $vendor */
                $vendor = $payoutRequest->vendor;
                if ((float) $vendor->balance < (float) $payoutRequest->amount) {
                    throw new \RuntimeException('Satıcı bakiyesi yetersiz.');
                }
                $vendor->decrement('balance', $payoutRequest->amount);
                $payoutRequest->update([
                    'status' => 'approved',
                    'admin_note' => $request->input('admin_note'),
                    'processed_at' => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), null, 422);
        }

        return $this->ok($payoutRequest->fresh(), 'Talep onaylandı ve bakiyeden düşüldü.');
    }

    public function reject(Request $request, PayoutRequest $payoutRequest)
    {
        if ($payoutRequest->status !== 'pending') {
            return $this->fail('Bu talep zaten işlendi.', null, 422);
        }
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $payoutRequest->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'] ?? null,
            'processed_at' => now(),
        ]);

        return $this->ok($payoutRequest->fresh(), 'Talep reddedildi.');
    }
}
