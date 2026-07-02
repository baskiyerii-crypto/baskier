<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressPlaceHint extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'district_id',
        'cadde',
        'sokak',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];
}
