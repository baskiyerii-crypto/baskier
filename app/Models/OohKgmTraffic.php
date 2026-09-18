<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OohKgmTraffic extends Model
{
    protected $table = 'ooh_kgm_traffic';

    protected $fillable = [
        'road_ref', 'name', 'aadt', 'year', 'lat', 'lng',
    ];

    protected $casts = [
        'aadt' => 'integer',
        'year' => 'integer',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];
}
