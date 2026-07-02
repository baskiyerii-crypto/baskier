<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $fillable = [
        'quote_request_id', 'vendor_id', 'amount', 'delivery_days', 'note', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
