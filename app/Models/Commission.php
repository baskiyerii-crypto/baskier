<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $fillable = [
        'order_id', 'vendor_id', 'rate', 'amount', 'vendor_net', 'payout_status',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'vendor_net' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
