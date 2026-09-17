<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OohPlanItem extends Model
{
    protected $fillable = [
        'ooh_plan_id', 'ooh_inventory_id', 'owner_vendor_id',
        'starts_on', 'ends_on', 'list_price_snapshot',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'list_price_snapshot' => 'decimal:2',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(OohPlan::class, 'ooh_plan_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(OohInventory::class, 'ooh_inventory_id');
    }

    public function ownerVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'owner_vendor_id');
    }

    public function occupancies(): HasMany
    {
        return $this->hasMany(OohOccupancy::class);
    }
}
