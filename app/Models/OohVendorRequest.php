<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OohVendorRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_QUOTED = 'quoted';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'ooh_plan_id', 'vendor_id', 'status', 'vendor_consented',
        'quoted_at', 'resolved_at',
    ];

    protected $casts = [
        'vendor_consented' => 'boolean',
        'quoted_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(OohPlan::class, 'ooh_plan_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(OohQuote::class);
    }

    public function latestQuote(): HasOne
    {
        return $this->hasOne(OohQuote::class)->latestOfMany();
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'ooh_vendor_request_id');
    }
}
