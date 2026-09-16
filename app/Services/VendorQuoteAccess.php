<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\Vendor;

class VendorQuoteAccess
{
    public function moduleEnabled(Vendor $vendor, ?QuoteRequest $request = null): bool
    {
        return $request?->request_type === 'freelancer'
            ? $vendor->hasActiveFreelancerModule()
            : $vendor->hasActiveQuotesModule();
    }

    public function categoryAllowed(Vendor $vendor, QuoteRequest $request): bool
    {
        $ids = $vendor->quoteCategories()->pluck('categories.id');

        // The freelancer inbox accepts all categories when none are selected.
        if ($request->request_type === 'freelancer') {
            return $ids->isEmpty() || $ids->contains($request->category_id);
        }

        if ($ids->isEmpty()) {
            $ids = $vendor->products()->pluck('category_id')->unique()->filter();
        }

        return $ids->contains($request->category_id)
            || $request->items()->whereIn('category_id', $ids)->exists();
    }
}
