<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\PriceSnapshot;
use App\Models\Product;
use App\Models\Quote;

class PricingEstimateService
{
    /**
     * @return array{estimated_min: float, estimated_max: float, confidence: string, disclaimer: string}
     */
    public function estimateForProduct(Product $product): array
    {
        $disclaimer = 'Bu aralık tahmindir; kesin fiyat teklif veya sipariş onayı ile belirlenir.';

        $vendor = $product->vendor;
        if ($product->pricing_type === 'quote' || $product->catalog_type === 'quote_only') {
            return [
                'estimated_min' => 0.0,
                'estimated_max' => 0.0,
                'confidence' => 'none',
                'disclaimer' => $disclaimer,
            ];
        }

        if ($product->price_min !== null && $product->price_max !== null && (float) $product->price_max >= (float) $product->price_min) {
            $snap = $this->storeSnapshot($product, (float) $product->price_min, (float) $product->price_max, 'high');

            return [
                'estimated_min' => (float) $snap->estimated_min,
                'estimated_max' => (float) $snap->estimated_max,
                'confidence' => 'high',
                'disclaimer' => $disclaimer,
            ];
        }

        $historical = $this->historicalPricesForShopCategory($vendor->id, $product->category_id);
        $acceptedQuotes = $this->acceptedQuotesForShopCategory($vendor->id, $product->category_id);

        $pool = array_values(array_filter(array_merge($historical, $acceptedQuotes)));
        $pool[] = (float) $product->price;

        $pool = $this->removeOutliers($pool);
        if ($pool === []) {
            $p = (float) $product->price;

            return [
                'estimated_min' => round($p * 0.9, 2),
                'estimated_max' => round($p * 1.1, 2),
                'confidence' => 'low',
                'disclaimer' => $disclaimer,
            ];
        }

        sort($pool);
        $median = $pool[(int) floor((count($pool) - 1) / 2)];
        $min = round($median * 0.85, 2);
        $max = round($median * 1.2, 2);
        $confidence = count($pool) >= 8 ? 'medium' : 'low';

        $snap = $this->storeSnapshot($product, $min, $max, $confidence);

        return [
            'estimated_min' => (float) $snap->estimated_min,
            'estimated_max' => (float) $snap->estimated_max,
            'confidence' => $confidence,
            'disclaimer' => $disclaimer,
        ];
    }

    /**
     * Birden fazla ürün ve adet için toplu tahmin (iş kalemleri).
     *
     * @param  list<array{product_id: int, quantity: int}>  $lines
     * @return array{lines: list<array<string, mixed>>, totals: array{estimated_min: float, estimated_max: float}, overall_confidence: string, has_quote_only_lines: bool, disclaimer: string}
     */
    public function estimateBundle(array $lines): array
    {
        $merged = [];
        foreach ($lines as $line) {
            $pid = (int) ($line['product_id'] ?? 0);
            $qty = max(1, (int) ($line['quantity'] ?? 1));
            if ($pid < 1) {
                continue;
            }
            $merged[$pid] = ($merged[$pid] ?? 0) + $qty;
        }

        $disclaimer = 'Her kalem ve adet satıcıya göre değişebilir; toplam tutar tahmindir. Kesin fiyat sipariş veya teklifle netleşir.';

        if ($merged === []) {
            return [
                'lines' => [],
                'totals' => ['estimated_min' => 0.0, 'estimated_max' => 0.0],
                'overall_confidence' => 'none',
                'has_quote_only_lines' => false,
                'disclaimer' => $disclaimer,
            ];
        }

        $products = Product::query()
            ->whereIn('id', array_keys($merged))
            ->where('is_active', true)
            ->with(['vendor', 'category'])
            ->get()
            ->keyBy('id');

        $outLines = [];
        $sumMin = 0.0;
        $sumMax = 0.0;
        $hasQuoteOnly = false;
        $qualityRank = ['high' => 3, 'medium' => 2, 'low' => 1];
        $minQuality = 999;

        foreach ($merged as $pid => $qty) {
            $product = $products->get($pid);
            if (! $product) {
                continue;
            }

            $est = $this->estimateForProduct($product);
            $isQuote = ($est['confidence'] === 'none'
                || ((float) $est['estimated_min'] <= 0.0 && (float) $est['estimated_max'] <= 0.0));

            if ($isQuote) {
                $hasQuoteOnly = true;
                $lineMin = 0.0;
                $lineMax = 0.0;
            } else {
                $lineMin = round((float) $est['estimated_min'] * $qty, 2);
                $lineMax = round((float) $est['estimated_max'] * $qty, 2);
                $sumMin += $lineMin;
                $sumMax += $lineMax;
                $minQuality = min($minQuality, $qualityRank[$est['confidence']] ?? 1);
            }

            $outLines[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'quantity' => $qty,
                'estimated_min' => $lineMin,
                'estimated_max' => $lineMax,
                'confidence' => $est['confidence'],
                'is_quote_only' => $isQuote,
                'vendor_name' => $product->vendor?->name,
                'category_name' => $product->category?->name,
            ];
        }

        $nonQuoteExists = false;
        foreach ($outLines as $l) {
            if (! $l['is_quote_only']) {
                $nonQuoteExists = true;
                break;
            }
        }

        $overall = 'none';
        if ($nonQuoteExists) {
            $overall = match ($minQuality) {
                3 => 'high',
                2 => 'medium',
                default => 'low',
            };
        }

        return [
            'lines' => $outLines,
            'totals' => [
                'estimated_min' => round($sumMin, 2),
                'estimated_max' => round($sumMax, 2),
            ],
            'overall_confidence' => $overall,
            'has_quote_only_lines' => $hasQuoteOnly,
            'disclaimer' => $disclaimer,
        ];
    }

    /**
     * @param  list<float>  $values
     * @return list<float>
     */
    private function removeOutliers(array $values): array
    {
        if (count($values) < 4) {
            return $values;
        }
        sort($values);
        $q1 = $values[(int) floor((count($values) - 1) * 0.25)];
        $q3 = $values[(int) floor((count($values) - 1) * 0.75)];
        $iqr = max($q3 - $q1, 0.0001);
        $low = $q1 - 1.5 * $iqr;
        $high = $q3 + 1.5 * $iqr;

        return array_values(array_filter($values, fn ($v) => $v >= $low && $v <= $high));
    }

    /**
     * @return list<float>
     */
    private function historicalPricesForShopCategory(int $vendorId, int $categoryId): array
    {
        return OrderItem::query()
            ->whereHas('order', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)->where('status', 'completed');
            })
            ->whereHas('product', fn ($q) => $q->where('category_id', $categoryId))
            ->whereNotNull('product_id')
            ->orderByDesc('id')
            ->limit(40)
            ->pluck('price')
            ->map(fn ($p) => (float) $p)
            ->all();
    }

    /**
     * @return list<float>
     */
    private function acceptedQuotesForShopCategory(int $vendorId, int $categoryId): array
    {
        return Quote::query()
            ->where('vendor_id', $vendorId)
            ->where('status', 'selected')
            ->whereHas('quoteRequest', fn ($q) => $q->where('category_id', $categoryId))
            ->orderByDesc('id')
            ->limit(30)
            ->pluck('amount')
            ->map(fn ($p) => (float) $p)
            ->all();
    }

    private function storeSnapshot(Product $product, float $min, float $max, string $confidence): PriceSnapshot
    {
        return PriceSnapshot::create([
            'product_id' => $product->id,
            'quote_request_id' => null,
            'vendor_id' => $product->vendor_id,
            'estimated_min' => $min,
            'estimated_max' => max($max, $min),
            'confidence' => $confidence,
            'computed_at' => now(),
        ]);
    }
}
