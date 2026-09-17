<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorMember extends Model
{
    public const ROLE_OWNER = 'owner';

    public const ROLE_OPS = 'ops';

    public const ROLE_FIELD = 'field';

    protected $fillable = ['vendor_id', 'user_id', 'staff_role'];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOwner(): bool
    {
        return $this->staff_role === self::ROLE_OWNER;
    }
}
