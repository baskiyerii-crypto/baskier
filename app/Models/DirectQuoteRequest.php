<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectQuoteRequest extends Model
{
    protected $fillable = [
        'customer_user_id', 'vendor_id', 'title', 'body', 'status',
        'offer_amount', 'vendor_note', 'vendor_consented', 'closed_at',
    ];

    protected $casts = [
        'offer_amount' => 'decimal:2',
        'vendor_consented' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
