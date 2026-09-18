<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutdoorCrew extends Model
{
    protected $fillable = ['vendor_id', 'name'];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(VendorMember::class, 'crew_id');
    }

    public function grants(): HasMany
    {
        return $this->hasMany(OohInventoryGrant::class, 'crew_id');
    }
}
