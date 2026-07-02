<?php

namespace App\Policies;

use App\Models\QuoteRequest;
use App\Models\User;

class QuoteRequestPolicy
{
    public function view(User $user, QuoteRequest $quoteRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($quoteRequest->user_id === $user->id) {
            return true;
        }
        if ($user->vendor_id) {
            return $this->vendorCanAccessQuoteRequest($user, $quoteRequest);
        }

        return false;
    }

    public function uploadFiles(User $user, QuoteRequest $quoteRequest): bool
    {
        return $quoteRequest->user_id === $user->id;
    }

    public function vendorSubmitQuote(User $user, QuoteRequest $quoteRequest): bool
    {
        if (! $user->vendor_id) {
            return false;
        }

        return $this->vendorCanAccessQuoteRequest($user, $quoteRequest);
    }

    private function vendorCanAccessQuoteRequest(User $user, QuoteRequest $quoteRequest): bool
    {
        $vendor = $user->vendor;
        if (! $vendor) {
            return false;
        }
        $categoryIds = $vendor->quoteCategories()->pluck('categories.id');
        if ($categoryIds->isEmpty()) {
            $categoryIds = $vendor->products()->pluck('category_id')->unique()->filter();
        }

        return $categoryIds->contains($quoteRequest->category_id);
    }
}
