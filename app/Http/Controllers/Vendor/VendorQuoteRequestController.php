<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\Quote;
use App\Models\QuoteMeetingCharge;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorQuoteRequestController extends Controller
{
    private function ensureQuotesModuleEnabled($vendor, ?QuoteRequest $quoteRequest = null)
    {
        if (! app(\App\Services\VendorQuoteAccess::class)->moduleEnabled($vendor, $quoteRequest)) {
            return redirect()
                ->route('vendor.subscriptions.index')
                ->with('error', 'Bu talep için gerekli abonelik aktif değil. Aboneliklerinizi kontrol edin.');
        }

        return null;
    }

    private function getVendor(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403, 'Satici hesabi bulunamadi.');
        }
        return $vendor;
    }

    public function index(Request $request)
    {
        $vendor = $this->getVendor($request);
        if ($response = $this->ensureQuotesModuleEnabled($vendor)) {
            return $response;
        }
        $categoryIds = $vendor->quoteCategories()->pluck('categories.id');
        if ($categoryIds->isEmpty()) {
            $categoryIds = $vendor->products()->pluck('category_id')->unique()->filter();
        }
        $query = QuoteRequest::with(['category', 'user', 'items.category', 'items.product'])
            ->where('status', 'open')
            ->when(
                \Illuminate\Support\Facades\Schema::hasColumn('quote_requests', 'request_type'),
                fn ($q) => $q->where('request_type', 'physical_quote')
            )
            ->where(function ($q) use ($categoryIds) {
                $ids = $categoryIds->isEmpty() ? [0] : $categoryIds->values();
                $q->whereIn('category_id', $ids)
                    ->orWhereHas('items', fn ($iq) => $iq->whereIn('category_id', $ids));
            });
        if ($vendor->city) {
            $query->where(function ($q) use ($vendor) {
                $q->where('city', $vendor->city)->orWhereNull('city');
            });
        }
        $quoteRequests = $query->latest()->paginate(15);
        $myQuotes = Quote::where('vendor_id', $vendor->id)->pluck('quote_request_id')->unique();
        return view('vendor.quote-requests.index', compact('quoteRequests', 'vendor', 'myQuotes'));
    }

    public function show(Request $request, QuoteRequest $quoteRequest)
    {
        $vendor = $this->getVendor($request);
        if ($response = $this->ensureQuotesModuleEnabled($vendor, $quoteRequest)) {
            return $response;
        }
        abort_unless(app(\App\Services\VendorQuoteAccess::class)->categoryAllowed($vendor, $quoteRequest), 403, 'Bu kategoriye teklif veremezsiniz.');
        $quoteRequest->load([
            'category',
            'user',
            'items.category',
            'items.product',
            'items.files',
            'quotes' => fn ($q) => $q->where('vendor_id', $vendor->id),
        ]);
        $hasPaidMeeting = QuoteMeetingCharge::where('quote_request_id', $quoteRequest->id)->where('vendor_id', $vendor->id)->exists();
        $myQuote = $quoteRequest->quotes->first();
        return view('vendor.quote-requests.show', compact('quoteRequest', 'vendor', 'hasPaidMeeting', 'myQuote'));
    }

    public function acceptMeeting(Request $request, QuoteRequest $quoteRequest)
    {
        $vendor = $this->getVendor($request);
        if ($response = $this->ensureQuotesModuleEnabled($vendor, $quoteRequest)) {
            return $response;
        }
        abort_unless(app(\App\Services\VendorQuoteAccess::class)->categoryAllowed($vendor, $quoteRequest), 403, 'Bu kategoriye teklif veremezsiniz.');
        if (! $quoteRequest->isOpen()) {
            return back()->with('error', 'Bu talep kapanmış. Açık talepleri inceleyebilirsiniz.');
        }
        $fee = Setting::meetingFee();
        if ($vendor->balance < $fee) {
            return back()->with('error', 'Bakiye yetersiz. Gorusme ucreti: ' . number_format($fee, 2) . ' TL. Bakiye yukleyin.');
        }
        if (QuoteMeetingCharge::where('quote_request_id', $quoteRequest->id)->where('vendor_id', $vendor->id)->exists()) {
            return back()->with('error', 'Bu talebe zaten gorusme ucreti odediniz.');
        }
        DB::transaction(function () use ($vendor, $quoteRequest, $fee) {
            $vendor->decrement('balance', $fee);
            QuoteMeetingCharge::create([
                'quote_request_id' => $quoteRequest->id,
                'vendor_id' => $vendor->id,
                'amount' => $fee,
            ]);
            $vendor->balanceTransactions()->create([
                'amount' => -$fee,
                'type' => 'meeting_charge',
                'reference_type' => 'quote_request',
                'reference_id' => $quoteRequest->id,
                'description' => 'Teklif talebi gorusme ucreti #' . $quoteRequest->id,
                'balance_after' => $vendor->fresh()->balance,
            ]);
        });
        return redirect()->route('vendor.quote-requests.show', $quoteRequest)->with('success', 'Gorusme ucreti alindi. Talep detaylari gorunur.');
    }

    public function submitQuote(Request $request, QuoteRequest $quoteRequest)
    {
        $vendor = $this->getVendor($request);
        if ($response = $this->ensureQuotesModuleEnabled($vendor, $quoteRequest)) {
            return $response;
        }
        abort_unless(app(\App\Services\VendorQuoteAccess::class)->categoryAllowed($vendor, $quoteRequest), 403, 'Bu kategoriye teklif veremezsiniz.');
        if (! $quoteRequest->isOpen()) {
            return back()->with('error', 'Bu talep kapanmış. Açık talepleri inceleyebilirsiniz.');
        }
        if (! QuoteMeetingCharge::where('quote_request_id', $quoteRequest->id)->where('vendor_id', $vendor->id)->exists()) {
            return back()->with('error', 'Once bu talebe gorusme hakki almalisiniz.');
        }
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'delivery_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $quote = Quote::updateOrCreate(
            ['quote_request_id' => $quoteRequest->id, 'vendor_id' => $vendor->id],
            array_merge($validated, ['status' => 'pending'])
        );
        if ($quoteRequest->user) {
            try {
                $quoteRequest->user->notify(new \App\Notifications\QuoteOfferReceivedNotification($quote));
            } catch (\Throwable) {
            }
        }
        return back()->with('success', 'Teklifiniz gonderildi.');
    }
}
