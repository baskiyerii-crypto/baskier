<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class AdminVendorPayoutRequestController extends Controller
{
    public function __construct(private PayoutService $payouts) {}

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
        try {
            $this->payouts->approve($payoutRequest, $request->input('admin_note'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Talep onaylandı; bakiyeden düşüldü. Havale kaydı tamamlandı olarak işaretlendi.');
    }

    public function reject(Request $request, PayoutRequest $payoutRequest)
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        try {
            $this->payouts->reject($payoutRequest, $validated['admin_note'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Talep reddedildi.');
    }
}
