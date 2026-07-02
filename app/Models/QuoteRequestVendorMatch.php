<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequestVendorMatch extends Model
{
    protected $fillable = ['quote_request_id', 'vendor_id', 'score'];

    protected $casts = [
        'score' => 'decimal:4',
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
