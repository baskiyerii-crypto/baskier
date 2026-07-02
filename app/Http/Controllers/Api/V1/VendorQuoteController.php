<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Quote;
use App\Models\QuoteMeetingCharge;
use App\Models\QuoteRequest;
use App\Models\Setting;
use App\Notifications\QuoteOfferReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorQuoteController extends ApiController
{
    public function purchaseMeeting(Request $request, QuoteRequest $quoteRequest)
    {
        $this->authorize('vendorSubmitQuote', $quoteRequest);

        $vendor = $request->user()->vendor;
        if (! $vendor) {
            return $this->fail('Satıcı hesabı yok.', null, 403);
        }
        if (! $vendor->hasActiveQuotesModule()) {
            return $this->fail('Teklif modülü aktif değil.', null, 403);
        }
        if ($vendor->verification_status !== 'approved') {
            return $this->fail('Mağaza doğrulaması onaylı değil.', null, 403);
        }
        if (QuoteMeetingCharge::where('quote_request_id', $quoteRequest->id)->where('vendor_id', $vendor->id)->exists()) {
            return $this->fail('Bu talep için görüşme hakkı zaten satın alınmış.', null, 409);
        }

        $fee = Setting::meetingFee();
        if ($vendor->balance < $fee) {
            return $this->fail('Görüşme ücreti için bakiye yetersiz.', ['required_balance' => $fee], 422);
        }

        $charge = DB::transaction(function () use ($vendor, $quoteRequest, $fee) {
            $vendor->decrement('balance', $fee);

            $charge = QuoteMeetingCharge::create([
                'quote_request_id' => $quoteRequest->id,
                'vendor_id' => $vendor->id,
                'amount' => $fee,
            ]);

            $vendor->balanceTransactions()->create([
                'amount' => -$fee,
                'type' => 'meeting_charge',
                'reference_type' => 'quote_request',
                'reference_id' => $quoteRequest->id,
                'description' => 'Teklif talebi görüşme ücreti #'.$quoteRequest->id,
                'balance_after' => $vendor->fresh()->balance,
            ]);

            return $charge;
        });

        return $this->ok([
            'meeting_charge' => $charge,
            'vendor_balance' => (string) $vendor->fresh()->balance,
        ], 'Görüşme hakkı satın alındı.');
    }

    public function store(Request $request, QuoteRequest $quoteRequest)
    {
        $this->authorize('vendorSubmitQuote', $quoteRequest);

        $vendor = $request->user()->vendor;
        if (! $vendor->hasActiveQuotesModule()) {
            return $this->fail('Teklif modülü aktif değil.', null, 403);
        }
        if ($vendor->verification_status !== 'approved') {
            return $this->fail('Mağaza doğrulaması onaylı değil.', null, 403);
        }

        $fee = Setting::meetingFee();
        if ($vendor->balance < $fee) {
            return $this->fail('Görüşme ücreti için bakiye yetersiz. Önce bakiye yükleyin veya web panelden görüşme satın alın.', null, 422);
        }
        if (! QuoteMeetingCharge::where('quote_request_id', $quoteRequest->id)->where('vendor_id', $vendor->id)->exists()) {
            return $this->fail('Bu talep için önce görüşme hakkı satın alınmalı (web panel).', null, 422);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'delivery_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'note' => ['nullable', 'string', 'max:1000'],
            'included_notes' => ['nullable', 'string', 'max:2000'],
            'excluded_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $quote = DB::transaction(function () use ($quoteRequest, $vendor, $validated) {
            $hadQuote = Quote::where('quote_request_id', $quoteRequest->id)->where('vendor_id', $vendor->id)->exists();
            $quote = Quote::updateOrCreate(
                ['quote_request_id' => $quoteRequest->id, 'vendor_id' => $vendor->id],
                array_merge($validated, ['status' => 'pending'])
            );
            if (! $hadQuote) {
                $vendor->increment('quotes_answered');
            }

            return $quote;
        });

        if ($quoteRequest->user) {
            $quoteRequest->user->notify(new QuoteOfferReceivedNotification($quote));
        }

        return $this->ok($quote->load('vendor'), 'Teklif kaydedildi.', null, 201);
    }
}
