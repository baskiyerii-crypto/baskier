<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OohPlan extends Model
{
    public const PLANNER_CUSTOMER = 'customer';

    public const PLANNER_VENDOR = 'vendor';

    public const STATUS_PENDING_QUOTES = 'pending_quotes';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'planner_type', 'planner_user_id', 'planner_vendor_id',
        'title', 'note', 'status',
    ];

    public function planner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'planner_user_id');
    }

    public function plannerVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'planner_vendor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OohPlanItem::class);
    }

    public function vendorRequests(): HasMany
    {
        return $this->hasMany(OohVendorRequest::class);
    }

    public function estimatedTotal(): float
    {
        return (float) $this->items->sum(fn (OohPlanItem $item) => (float) ($item->list_price_snapshot ?? 0));
    }

    public function quotedTotal(): ?float
    {
        $requests = $this->vendorRequests;
        if ($requests->isEmpty()) {
            return null;
        }
        $quoted = $requests->filter(fn (OohVendorRequest $r) => $r->latestQuote !== null);
        if ($quoted->isEmpty()) {
            return null;
        }

        return (float) $quoted->sum(fn (OohVendorRequest $r) => (float) $r->latestQuote->amount);
    }
}
