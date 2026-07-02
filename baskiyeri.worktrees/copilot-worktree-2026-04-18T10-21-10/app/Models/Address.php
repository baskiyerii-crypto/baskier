<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'label', 'full_name', 'phone', 'city', 'district',
        'line1', 'line2', 'postal_code', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAttribute(): string
    {
        $parts = array_filter([
            $this->line1,
            $this->line2,
            trim(($this->district ? $this->district . ' / ' : '') . $this->city),
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }
}
