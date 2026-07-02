<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequestItem extends Model
{
    protected $fillable = [
        'quote_request_id',
        'category_id',
        'product_id',
        'quantity',
        'unit',
        'spec',
        'sort_order',
    ];

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(QuoteRequestItemFile::class);
    }
}

