<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    protected $fillable = [
        'vendor_id', 'amount', 'iban', 'account_holder', 'status', 'source',
        'admin_note', 'processed_at', 'requested_at', 'approved_at', 'rejected_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function requestedAt(): ?\Illuminate\Support\Carbon
    {
        return $this->requested_at ?? $this->created_at;
    }
}
