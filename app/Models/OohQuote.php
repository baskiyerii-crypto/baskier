<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OohQuote extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SELECTED = 'selected';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'ooh_vendor_request_id', 'vendor_id', 'amount', 'note',
        'vendor_consented', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'vendor_consented' => 'boolean',
    ];

    public function vendorRequest(): BelongsTo
    {
        return $this->belongsTo(OohVendorRequest::class, 'ooh_vendor_request_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
