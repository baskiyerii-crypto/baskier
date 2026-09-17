<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\Vendor;

class VendorQuoteAccess
{
    public function moduleEnabled(Vendor $vendor, ?QuoteRequest $request = null): bool
    {
        return match ($request?->request_type) {
            'freelancer' => $vendor->hasActiveFreelancerModule(),
            'tabela' => $vendor->hasActiveTabelaModule(),
            'ozalit' => $vendor->hasActiveOzalitModule(),
            default => $vendor->hasActiveQuotesModule(),
        };
    }

    public function categoryAllowed(Vendor $vendor, QuoteRequest $request): bool
    {
        $ids = $vendor->quoteCategories()->pluck('categories.id');

        if (in_array($request->request_type, ['freelancer', 'tabela', 'ozalit'], true)) {
            return $ids->isEmpty() || $ids->contains($request->category_id)
                || $request->items()->whereIn('category_id', $ids)->exists();
        }

        if ($ids->isEmpty()) {
            $ids = $vendor->products()->pluck('category_id')->unique()->filter();
        }

        return $ids->contains($request->category_id)
            || $request->items()->whereIn('category_id', $ids)->exists();
    }
}
