<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorCategoryRequest extends Model
{
    protected $fillable = [
        'vendor_id', 'category_ids', 'channel', 'status', 'admin_note', 'reviewed_at',
    ];

    protected $casts = [
        'category_ids' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
