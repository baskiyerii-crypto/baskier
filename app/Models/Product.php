<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'main_image',
        'price',
        'stock',
        'is_featured',
        'is_active',
        'attributes',
        'short_description',
        'description',
        'product_type',
        'digital_link',
        'catalog_type',
        'pricing_type',
        'listing_status',
        'price_min',
        'price_max',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_featured' => 'bool',
        'is_active' => 'bool',
        'price' => 'decimal:2',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function isDigital(): bool
    {
        return $this->product_type === 'digital';
    }
}
