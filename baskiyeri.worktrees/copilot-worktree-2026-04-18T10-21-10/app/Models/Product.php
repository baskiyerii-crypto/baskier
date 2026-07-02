<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_featured' => 'bool',
        'is_active' => 'bool',
        'price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isDigital(): bool
    {
        return $this->product_type === 'digital';
    }
}
