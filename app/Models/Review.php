<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = ['user_id', 'vendor_id', 'order_id', 'product_id', 'rating', 'comment'];

    protected static function booted(): void
    {
        static::created(function (Review $review) {
            $review->vendor->recalculateRating();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Bu ürünle ilişkili yorumlar: doğrudan product_id veya sipariş kaleminde ürün.
     */
    public static function queryForProduct(int $productId): Builder
    {
        return static::query()
            ->where(function (Builder $q) use ($productId) {
                $q->where('product_id', $productId)
                    ->orWhereHas('order.items', function (Builder $iq) use ($productId) {
                        $iq->where('product_id', $productId);
                    });
            });
    }
}
