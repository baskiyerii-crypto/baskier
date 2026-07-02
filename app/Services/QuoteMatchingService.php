<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\QuoteRequestVendorMatch;
use App\Models\Vendor;
use App\Notifications\QuoteRequestMatchedNotification;
use Illuminate\Support\Facades\DB;

class QuoteMatchingService
{
    /**
     * Score vendors for a quote request and persist matches; notify top matches.
     *
     * @return list<array{vendor: Vendor, score: float}>
     */
    public function matchAndNotify(QuoteRequest $request, int $notifyTop = 8): array
    {
        $request->loadMissing('category');

        $vendors = Vendor::query()
            ->where('is_active', true)
            ->where('verification_status', 'approved')
            ->whereHas('user', fn ($q) => $q->where('role', 'vendor')->where('status', 'active'))
            ->with(['quoteCategories', 'user'])
            ->get();

        $scored = [];
        foreach ($vendors as $vendor) {
            $score = $this->scoreVendor($request, $vendor);
            if ($score <= 0) {
                continue;
            }
            $scored[] = ['vendor' => $vendor, 'score' => $score];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        DB::transaction(function () use ($request, $scored) {
            QuoteRequestVendorMatch::where('quote_request_id', $request->id)->delete();
            foreach ($scored as $row) {
                QuoteRequestVendorMatch::create([
                    'quote_request_id' => $request->id,
                    'vendor_id' => $row['vendor']->id,
                    'score' => $row['score'],
                ]);
            }
        });

        foreach (array_slice($scored, 0, $notifyTop) as $row) {
            $user = $row['vendor']->user;
            if ($user) {
                $user->notify(new QuoteRequestMatchedNotification($request));
            }
        }

        return $scored;
    }

    private function scoreVendor(QuoteRequest $request, Vendor $vendor): float
    {
        $score = 0.0;

        $categoryIds = $vendor->quoteCategories()->pluck('categories.id');
        if ($categoryIds->isEmpty()) {
            $categoryIds = $vendor->products()->pluck('category_id')->unique()->filter()->values();
        }
        if ($categoryIds->contains($request->category_id)) {
            $score += 40;
        } else {
            return 0.0;
        }

        if ($vendor->city && $request->city && strcasecmp((string) $vendor->city, (string) $request->city) === 0) {
            $score += 25;
        } elseif ($request->city === null || $request->city === '') {
            $score += 10;
        } else {
            $score += 5;
        }

        $regions = $vendor->service_regions;
        if (is_array($regions) && $request->city && in_array($request->city, $regions, true)) {
            $score += 10;
        }

        if ($vendor->rating_average) {
            $score += min(10, (float) $vendor->rating_average * 2);
        }

        if ($vendor->quotes_answered > 0) {
            $rate = $vendor->quotes_won / max(1, $vendor->quotes_answered);
            $score += 10 * $rate;
        }

        if ($vendor->hasActiveQuotesModule()) {
            $score += 5;
        }

        return round($score, 4);
    }
}
