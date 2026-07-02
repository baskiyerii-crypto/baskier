<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'vendor_id', 'contractor_user_id', 'type', 'quote_id', 'freelancer_job_id',
        'status', 'subtotal', 'commission_rate', 'commission_amount', 'vendor_amount',
        'paid_at', 'delivered_at', 'commission_ready_at', 'payout_approved', 'payout_at',
        'shipping_address', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'vendor_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'delivered_at' => 'datetime',
        'commission_ready_at' => 'datetime',
        'payout_approved' => 'boolean',
        'payout_at' => 'datetime',
    ];

    public static function generateOrderNumber(): string
    {
        return 'BY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contractor_user_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isPaid(): bool
    {
        return $this->status !== 'pending' && $this->paid_at !== null;
    }

    public function isCommissionReady(): bool
    {
        return $this->commission_ready_at && $this->commission_ready_at->isPast() && !$this->payout_approved;
    }
}
