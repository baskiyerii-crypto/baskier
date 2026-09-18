<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OohLocationInsight extends Model
{
    public const PEDESTRIAN_HIGHWAY_NONE = 'highway_none';

    public const PEDESTRIAN_ZONE = 'pedestrian_zone';

    public const PEDESTRIAN_UNKNOWN = 'urban_unknown';

    public const VISIBILITY_LOW = 'low';

    public const VISIBILITY_MID = 'mid';

    public const VISIBILITY_HIGH = 'high';

    protected $fillable = [
        'ooh_inventory_id',
        'population_province',
        'population_district',
        'population_year',
        'road_class',
        'road_name',
        'road_ref',
        'maxspeed',
        'lanes',
        'vehicle_aadt',
        'vehicle_aadt_year',
        'vehicle_source',
        'pedestrian_kind',
        'visibility_band',
        'street_view_available',
        'enriched_at',
    ];

    protected $casts = [
        'population_province' => 'integer',
        'population_district' => 'integer',
        'population_year' => 'integer',
        'maxspeed' => 'integer',
        'lanes' => 'integer',
        'vehicle_aadt' => 'integer',
        'vehicle_aadt_year' => 'integer',
        'street_view_available' => 'boolean',
        'enriched_at' => 'datetime',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(OohInventory::class, 'ooh_inventory_id');
    }

    public function pedestrianLabel(): string
    {
        return match ($this->pedestrian_kind) {
            self::PEDESTRIAN_HIGHWAY_NONE => 'Yaya beklenmez (otoyol / hızlı yol)',
            self::PEDESTRIAN_ZONE => 'Yaya bölgesi (sayı yok; resmi sayım bulunmuyor)',
            default => 'Yaya sayımı yok',
        };
    }

    public function visibilityLabel(): string
    {
        return match ($this->visibility_band) {
            self::VISIBILITY_HIGH => 'Yüksek',
            self::VISIBILITY_MID => 'Orta',
            self::VISIBILITY_LOW => 'Düşük',
            default => 'Hesaplanamadı',
        };
    }
}
