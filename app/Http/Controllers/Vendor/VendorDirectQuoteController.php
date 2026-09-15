<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\DirectQuoteRequest;
use Illuminate\Http\Request;

class VendorDirectQuoteController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        $items = DirectQuoteRequest::query()
            ->with('customer')
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(20);

        return view('vendor.direct-quotes.index', compact('vendor', 'items'));
    }

    public function show(Request $request, DirectQuoteRequest $directQuote)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor && $directQuote->vendor_id === $vendor->id, 403);
        $directQuote->load('customer');

        return view('vendor.direct-quotes.show', compact('vendor', 'directQuote'));
    }

    public function offer(Request $request, DirectQuoteRequest $directQuote)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor && $directQuote->vendor_id === $vendor->id, 403);

        $validated = $request->validate([
            'offer_amount' => ['required', 'numeric', 'min:1'],
            'vendor_note' => ['nullable', 'string', 'max:2000'],
            'share_my_contact' => ['accepted'],
        ]);

        $directQuote->update([
            'offer_amount' => $validated['offer_amount'],
            'vendor_note' => $validated['vendor_note'] ?? null,
            'vendor_consented' => true,
            'status' => 'offered',
        ]);

        return back()->with('success', __('panel.direct_quote_offer_sent'));
    }

    public function consent(Request $request, DirectQuoteRequest $directQuote)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor && $directQuote->vendor_id === $vendor->id, 403);
        $request->validate(['share_my_contact' => ['accepted']]);
        $directQuote->update(['vendor_consented' => true]);

        return back()->with('success', __('panel.vendor_consent_saved'));
    }
}
