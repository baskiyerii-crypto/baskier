<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceSnapshot extends Model
{
    protected $fillable = [
        'product_id', 'quote_request_id', 'vendor_id',
        'estimated_min', 'estimated_max', 'confidence', 'computed_at',
    ];

    protected $casts = [
        'estimated_min' => 'decimal:2',
        'estimated_max' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
