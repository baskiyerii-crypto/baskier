<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OohProof extends Model
{
    protected $fillable = [
        'ooh_occupancy_id', 'user_id', 'photo_path', 'lat', 'lng',
        'distance_m', 'is_valid', 'captured_at',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'is_valid' => 'boolean',
        'captured_at' => 'datetime',
        'distance_m' => 'integer',
    ];

    public function occupancy(): BelongsTo
    {
        return $this->belongsTo(OohOccupancy::class, 'ooh_occupancy_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
