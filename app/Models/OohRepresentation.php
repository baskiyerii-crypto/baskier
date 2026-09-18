<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OohRepresentation extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'owner_vendor_id',
        'agency_vendor_id',
        'status',
        'exclusive',
        'commission_rate',
        'notes',
        'invited_by_vendor_id',
        'accepted_at',
        'revoked_at',
    ];

    protected $casts = [
        'exclusive' => 'boolean',
        'commission_rate' => 'decimal:2',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'owner_vendor_id');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'agency_vendor_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'invited_by_vendor_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
