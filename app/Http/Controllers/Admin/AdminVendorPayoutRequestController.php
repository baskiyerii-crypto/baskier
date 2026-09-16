<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminVendorPayoutRequestController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('payout_requests')) {
            $requests = new LengthAwarePaginator([], 0, 25);

            return view('admin.vendor-payout-requests.index', compact('requests'));
        }

        $q = PayoutRequest::with('vendor')->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        $requests = $q->paginate(25)->withQueryString();

        return view('admin.vendor-payout-requests.index', compact('requests'));
    }

    public function approve(Request $request, PayoutRequest $payoutRequest)
    {
        if ($payoutRequest->status !== 'pending') {
            return back()->with('error', 'Bu talep zaten işlendi.');
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
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Talep onaylandı; bakiyeden düşüldü.');
    }

    public function reject(Request $request, PayoutRequest $payoutRequest)
    {
        if ($payoutRequest->status !== 'pending') {
            return back()->with('error', 'Bu talep zaten işlendi.');
        }
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $payoutRequest->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'] ?? null,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Talep reddedildi.');
    }
}
