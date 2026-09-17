<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OohOccupancy extends Model
{
    public const KIND_BLOCKED = 'blocked';

    public const KIND_HOLD = 'hold';

    public const KIND_BOOKED = 'booked';

    protected $fillable = [
        'ooh_inventory_id', 'ooh_plan_item_id', 'assigned_user_id',
        'starts_on', 'ends_on', 'kind', 'expires_at',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'expires_at' => 'datetime',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(OohInventory::class, 'ooh_inventory_id');
    }

    public function planItem(): BelongsTo
    {
        return $this->belongsTo(OohPlanItem::class, 'ooh_plan_item_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(OohProof::class);
    }

    public function isActiveHold(): bool
    {
        return $this->kind === self::KIND_HOLD
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
