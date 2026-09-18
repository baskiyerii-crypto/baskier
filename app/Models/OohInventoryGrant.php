<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OohInventoryGrant extends Model
{
    protected $fillable = [
        'vendor_id', 'crew_id', 'user_id', 'starts_at', 'ends_at', 'revoked_at', 'granted_by_user_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function crew(): BelongsTo
    {
        return $this->belongsTo(OutdoorCrew::class, 'crew_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }

    public function isActive(?\DateTimeInterface $at = null): bool
    {
        $now = $at ? \Carbon\Carbon::parse($at) : now();
        if ($this->revoked_at) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }

        return true;
    }
}
