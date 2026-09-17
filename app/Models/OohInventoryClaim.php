<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OohInventoryClaim extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'reporter_vendor_id', 'ooh_inventory_id', 'evidence',
        'permit_no', 'status', 'admin_note', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function reporterVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'reporter_vendor_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(OohInventory::class, 'ooh_inventory_id');
    }
}
