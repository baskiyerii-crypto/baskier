<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactShare extends Model
{
    protected $fillable = [
        'customer_user_id', 'vendor_id', 'context_type', 'context_id',
        'customer_consented', 'vendor_consented', 'shared_at',
    ];

    protected $casts = [
        'customer_consented' => 'boolean',
        'vendor_consented' => 'boolean',
        'shared_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isFullyConsented(): bool
    {
        return $this->customer_consented && $this->vendor_consented;
    }
}
