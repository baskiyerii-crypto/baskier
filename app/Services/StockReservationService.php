<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class StockReservationService
{
    /**
     * Reserve stock for a list of items within a transaction using row-level locking.
     *
     * @param array<int, array{product_id: int, variant_id: ?int, quantity: int}> $items
     * @return list<StockReservation>
     * @throws \InvalidArgumentException
     */
    public function reserveStock(array $items, string $checkoutToken, ?int $userId = null, int $ttlMinutes = 15): array
    {
        return DB::transaction(function () use ($items, $checkoutToken, $userId, $ttlMinutes) {
            // Cancel/release any previous reservations for this checkout token first
            $this->releaseReservations($checkoutToken);

            $reservations = [];
            $expiresAt = now()->addMinutes($ttlMinutes);

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $variantId = ! empty($item['variant_id']) ? (int) $item['variant_id'] : null;
                $quantity = max(1, (int) $item['quantity']);

                if ($variantId) {
                    $variant = ProductVariant::where('id', $variantId)->lockForUpdate()->first();
                    if (! $variant) {
                        throw new \InvalidArgumentException('Ürün varyantı bulunamadı.');
                    }

                    $reservedQuantity = StockReservation::query()
                        ->where('variant_id', $variantId)
                        ->where('status', 'reserved')
                        ->where('expires_at', '>', now())
                        ->where('checkout_token', '!=', $checkoutToken)
                        ->sum('quantity');

                    $availableStock = (int) $variant->stock - (int) $reservedQuantity;
                    if ($availableStock < $quantity) {
                        throw new \InvalidArgumentException("Yetersiz stok ({$variant->name}): Talep edilen {$quantity}, kalan {$availableStock}.");
                    }

                    $reservation = StockReservation::create([
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'user_id' => $userId,
                        'checkout_token' => $checkoutToken,
                        'quantity' => $quantity,
                        'status' => 'reserved',
                        'expires_at' => $expiresAt,
                    ]);

                    $reservations[] = $reservation;
                } else {
                    $product = Product::where('id', $productId)->lockForUpdate()->first();
                    if (! $product) {
                        throw new \InvalidArgumentException('Ürün bulunamadı.');
                    }

                    // Digital products do not need physical stock decrement if stock is zero/unlimited, but check physical products
                    if (! $product->isDigital()) {
                        $reservedQuantity = StockReservation::query()
                            ->where('product_id', $productId)
                            ->whereNull('variant_id')
                            ->where('status', 'reserved')
                            ->where('expires_at', '>', now())
                            ->where('checkout_token', '!=', $checkoutToken)
                            ->sum('quantity');

                        $availableStock = (int) $product->stock - (int) $reservedQuantity;
                        if ($availableStock < $quantity) {
                            throw new \InvalidArgumentException("Yetersiz stok ({$product->name}): Talep edilen {$quantity}, kalan {$availableStock}.");
                        }
                    }

                    $reservation = StockReservation::create([
                        'product_id' => $productId,
                        'variant_id' => null,
                        'user_id' => $userId,
                        'checkout_token' => $checkoutToken,
                        'quantity' => $quantity,
                        'status' => 'reserved',
                        'expires_at' => $expiresAt,
                    ]);

                    $reservations[] = $reservation;
                }
            }

            return $reservations;
        });
    }

    /**
     * Atomically commit reserved stock and apply final inventory deduction.
     */
    public function commitReservations(string $checkoutToken, Order $order): void
    {
        DB::transaction(function () use ($checkoutToken, $order) {
            $reservations = StockReservation::query()
                ->where('checkout_token', $checkoutToken)
                ->where('status', 'reserved')
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {
                if ($reservation->variant_id) {
                    $variant = ProductVariant::where('id', $reservation->variant_id)->lockForUpdate()->first();
                    if ($variant) {
                        $variant->decrement('stock', $reservation->quantity);
                    }
                } elseif ($reservation->product_id) {
                    $product = Product::where('id', $reservation->product_id)->lockForUpdate()->first();
                    if ($product && ! $product->isDigital()) {
                        $product->decrement('stock', $reservation->quantity);
                    }
                }

                $reservation->update([
                    'status' => 'committed',
                    'order_id' => $order->id,
                ]);
            }
        });
    }

    /**
     * Release all reserved stock for a checkout session.
     */
    public function releaseReservations(string $checkoutToken): void
    {
        StockReservation::query()
            ->where('checkout_token', $checkoutToken)
            ->where('status', 'reserved')
            ->update(['status' => 'released']);
    }

    /**
     * Release expired reservations.
     */
    public function cleanupExpired(): int
    {
        return StockReservation::query()
            ->where('status', 'reserved')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }
}
