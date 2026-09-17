<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class FairProductDiscoveryService
{
    /**
     * Get fair discovery products for home and discovery sections.
     * Uses daily deterministic vendor round-robin with category diversity limits.
     *
     * @param int $limit Max items to return
     * @param string|null $seedDate Date string (Y-m-d) for deterministic rotation
     * @param int|null $categoryId Optional category filter
     * @param int $maxPerCategory Max products per category to ensure diversity
     * @return Collection<int, Product>
     */
    public function getDiscoveryProducts(
        int $limit = 24,
        ?string $seedDate = null,
        ?int $categoryId = null,
        int $maxPerCategory = 4
    ): Collection {
        $date = $seedDate ?: date('Y-m-d');

        $query = Product::query()
            ->published()
            ->where('stock', '>', 0)
            ->whereHas('vendor', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['vendor', 'category', 'variants']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        /** @var EloquentCollection<int, Product> $allCandidates */
        $allCandidates = $query->get();

        if ($allCandidates->isEmpty()) {
            return collect();
        }

        // Group candidates by vendor_id
        $byVendor = $allCandidates->groupBy('vendor_id');

        // Deterministically sort vendors for this day
        $sortedVendorIds = $byVendor->keys()->sortBy(function ($vendorId) use ($date) {
            return hash_hmac('sha256', (string) $vendorId, $date);
        })->values();

        // Deterministically sort products inside each vendor
        $vendorQueues = [];
        foreach ($sortedVendorIds as $vendorId) {
            $products = $byVendor->get($vendorId)->sortBy(function (Product $p) use ($date) {
                return hash_hmac('sha256', (string) $p->id, $date);
            })->values()->all();

            $vendorQueues[$vendorId] = $products;
        }

        $selected = collect();
        $categoryCounts = [];

        // Round-Robin selection:
        // Round 1: At most 1 product per vendor (satisfies "Aynı satıcı ilk turda alanı dolduramaz")
        // Subsequent rounds: If limit not yet met, pick additional products round-by-round.
        while ($selected->count() < $limit) {
            $pickedInThisRound = false;

            foreach ($sortedVendorIds as $vendorId) {
                if ($selected->count() >= $limit) {
                    break;
                }

                if (! empty($vendorQueues[$vendorId])) {
                    // Find first product in this vendor's queue that satisfies category diversity
                    $pickedIndex = null;
                    foreach ($vendorQueues[$vendorId] as $idx => $prod) {
                        $catId = $prod->category_id ?? 0;
                        $currentCatCount = $categoryCounts[$catId] ?? 0;

                        if ($currentCatCount < $maxPerCategory) {
                            $pickedIndex = $idx;
                            break;
                        }
                    }

                    if ($pickedIndex !== null) {
                        $pickedProduct = $vendorQueues[$vendorId][$pickedIndex];
                        array_splice($vendorQueues[$vendorId], $pickedIndex, 1);

                        $selected->push($pickedProduct);
                        $catId = $pickedProduct->category_id ?? 0;
                        $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
                        $pickedInThisRound = true;
                    }
                }
            }

            // If a full round over all vendors picked nothing (due to category diversity limits),
            // but candidates remain, relax category constraint one item at a time so vendors can still be discovered.
            if (! $pickedInThisRound) {
                $relaxedPick = false;
                foreach ($sortedVendorIds as $vendorId) {
                    if ($selected->count() >= $limit) {
                        break;
                    }
                    if (! empty($vendorQueues[$vendorId])) {
                        $pickedProduct = array_shift($vendorQueues[$vendorId]);
                        $selected->push($pickedProduct);
                        $catId = $pickedProduct->category_id ?? 0;
                        $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
                        $relaxedPick = true;
                        break;
                    }
                }

                // If all queues are completely empty, terminate immediately
                if (! $relaxedPick) {
                    break;
                }
            }
        }

        return $selected;
    }
}
