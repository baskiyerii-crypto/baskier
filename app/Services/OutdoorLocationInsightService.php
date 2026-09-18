<?php

namespace App\Services;

use App\Models\OohInventory;
use App\Models\OohKgmTraffic;
use App\Models\OohLocationInsight;
use App\Models\Setting;
use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use App\Support\OutdoorSchema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OutdoorLocationInsightService
{
    public function enrich(OohInventory $inventory): ?OohLocationInsight
    {
        if (! OutdoorSchema::insightsReady()) {
            return null;
        }

        $inventory->loadMissing(['province', 'districtRel']);
        $osm = $this->fetchOsmRoad((float) $inventory->lat, (float) $inventory->lng);
        $traffic = $this->matchKgm($osm['ref'] ?? null, (float) $inventory->lat, (float) $inventory->lng);
        $province = $this->resolveProvince($inventory);
        $district = $this->resolveDistrict($inventory, $province);

        $payload = [
            'population_province' => $province?->population,
            'population_district' => $district?->population,
            'population_year' => $district?->population_year ?: $province?->population_year,
            'road_class' => $osm['highway'] ?? null,
            'road_name' => $osm['name'] ?? null,
            'road_ref' => $osm['ref'] ?? ($traffic['road_ref'] ?? null),
            'maxspeed' => $osm['maxspeed'] ?? null,
            'lanes' => $osm['lanes'] ?? null,
            'vehicle_aadt' => $traffic['aadt'] ?? null,
            'vehicle_aadt_year' => $traffic['year'] ?? null,
            'vehicle_source' => $traffic ? 'kgm' : null,
            'pedestrian_kind' => $this->pedestrianKind($osm['highway'] ?? null),
            'visibility_band' => $this->visibilityBand($inventory, $osm),
            'street_view_available' => $this->streetViewAvailable((float) $inventory->lat, (float) $inventory->lng),
            'enriched_at' => now(),
        ];

        return OohLocationInsight::query()->updateOrCreate(
            ['ooh_inventory_id' => $inventory->id],
            $payload
        );
    }

    public function ensure(OohInventory $inventory): ?OohLocationInsight
    {
        $inventory->loadMissing('insight');
        if ($inventory->insight) {
            return $inventory->insight;
        }

        return $this->enrich($inventory);
    }

    /**
     * @return array{highway:?string,name:?string,ref:?string,maxspeed:?int,lanes:?int}
     */
    private function fetchOsmRoad(float $lat, float $lng): array
    {
        $empty = ['highway' => null, 'name' => null, 'ref' => null, 'maxspeed' => null, 'lanes' => null];
        if (app()->runningUnitTests()) {
            return $empty;
        }

        try {
            $query = sprintf(
                '[out:json][timeout:8];way(around:80,%s,%s)[highway];out tags 1;',
                number_format($lat, 7, '.', ''),
                number_format($lng, 7, '.', '')
            );
            $response = Http::timeout(8)
                ->asForm()
                ->post('https://overpass-api.de/api/interpreter', ['data' => $query]);
            if (! $response->successful()) {
                return $empty;
            }
            $el = $response->json('elements.0.tags') ?? [];
            $ref = isset($el['ref']) ? $this->normalizeRef((string) $el['ref']) : null;

            return [
                'highway' => isset($el['highway']) ? (string) $el['highway'] : null,
                'name' => isset($el['name']) ? (string) $el['name'] : null,
                'ref' => $ref,
                'maxspeed' => isset($el['maxspeed']) ? (int) preg_replace('/\D+/', '', (string) $el['maxspeed']) : null,
                'lanes' => isset($el['lanes']) ? (int) $el['lanes'] : null,
            ];
        } catch (Throwable) {
            return $empty;
        }
    }

    /**
     * @return array{road_ref:string,aadt:int,year:int}|null
     */
    private function matchKgm(?string $ref, float $lat, float $lng): ?array
    {
        if (! Schema::hasTable('ooh_kgm_traffic')) {
            return null;
        }

        $candidates = collect();
        if ($ref) {
            $candidates = OohKgmTraffic::query()->where('road_ref', $ref)->get();
        }
        if ($candidates->isEmpty()) {
            $candidates = OohKgmTraffic::query()->whereNotNull('lat')->whereNotNull('lng')->get();
        }

        $best = null;
        $bestMeters = PHP_FLOAT_MAX;
        foreach ($candidates as $row) {
            if ($row->lat === null || $row->lng === null) {
                if ($ref && $row->road_ref === $ref) {
                    return ['road_ref' => $row->road_ref, 'aadt' => (int) $row->aadt, 'year' => (int) $row->year];
                }
                continue;
            }
            $meters = $this->haversine($lat, $lng, (float) $row->lat, (float) $row->lng);
            $limit = $ref && $row->road_ref === $ref ? 8000 : 150;
            if ($meters <= $limit && $meters < $bestMeters) {
                $bestMeters = $meters;
                $best = $row;
            }
        }

        if (! $best && $ref) {
            $row = OohKgmTraffic::query()->where('road_ref', $ref)->first();
            $best = $row;
        }

        if (! $best) {
            return null;
        }

        return ['road_ref' => $best->road_ref, 'aadt' => (int) $best->aadt, 'year' => (int) $best->year];
    }

    private function resolveProvince(OohInventory $inventory): ?TurkiyeIl
    {
        if ($inventory->province) {
            return $inventory->province;
        }
        if ($inventory->turkiye_il_id) {
            return TurkiyeIl::query()->find($inventory->turkiye_il_id);
        }
        if ($inventory->city) {
            return TurkiyeIl::query()->where('name', $inventory->city)->first();
        }

        return null;
    }

    private function resolveDistrict(OohInventory $inventory, ?TurkiyeIl $province): ?TurkiyeIlce
    {
        if ($inventory->districtRel) {
            return $inventory->districtRel;
        }
        if ($inventory->turkiye_ilce_id) {
            return TurkiyeIlce::query()->find($inventory->turkiye_ilce_id);
        }
        if ($inventory->district && $province) {
            return TurkiyeIlce::query()
                ->where('province_id', $province->id)
                ->where('name', $inventory->district)
                ->first();
        }

        return null;
    }

    private function pedestrianKind(?string $highway): string
    {
        if (in_array($highway, ['motorway', 'motorway_link', 'trunk', 'trunk_link'], true)) {
            return OohLocationInsight::PEDESTRIAN_HIGHWAY_NONE;
        }
        if (in_array($highway, ['pedestrian', 'living_street', 'footway'], true)) {
            return OohLocationInsight::PEDESTRIAN_ZONE;
        }

        return OohLocationInsight::PEDESTRIAN_UNKNOWN;
    }

    /**
     * @param  array{highway:?string,maxspeed:?int}  $osm
     */
    private function visibilityBand(OohInventory $inventory, array $osm): string
    {
        $width = (float) ($inventory->face_width_m ?: 0);
        $height = (float) ($inventory->face_height_m ?: 0);
        $area = $width * $height;
        $speed = (int) ($osm['maxspeed'] ?: 50);
        $highway = $osm['highway'] ?? '';
        $score = 0;
        if ($area >= 20) {
            $score += 2;
        } elseif ($area >= 8) {
            $score += 1;
        }
        if ($inventory->illuminated) {
            $score += 1;
        }
        if ($speed >= 70 || in_array($highway, ['motorway', 'trunk', 'primary'], true)) {
            $score += 1;
        }
        if ($score >= 3) {
            return OohLocationInsight::VISIBILITY_HIGH;
        }
        if ($score >= 1) {
            return OohLocationInsight::VISIBILITY_MID;
        }

        return OohLocationInsight::VISIBILITY_LOW;
    }

    private function streetViewAvailable(float $lat, float $lng): bool
    {
        if (! Setting::apiEnabled('google', false)) {
            return false;
        }
        $key = Setting::get('google_maps_api_key', '');
        if ($key === '' || app()->runningUnitTests()) {
            return false;
        }
        try {
            $response = Http::timeout(6)->get('https://maps.googleapis.com/maps/api/streetview/metadata', [
                'location' => $lat.','.$lng,
                'key' => $key,
            ]);

            return $response->successful() && $response->json('status') === 'OK';
        } catch (Throwable) {
            return false;
        }
    }

    public function streetViewEmbedSrc(OohInventory $inventory): ?string
    {
        $insight = $inventory->insight;
        if (! $insight?->street_view_available || ! Setting::apiEnabled('google', false)) {
            return null;
        }
        $key = Setting::get('google_maps_api_key', '');
        if ($key === '') {
            return null;
        }

        return 'https://www.google.com/maps/embed/v1/streetview?key='.urlencode($key)
            .'&location='.(float) $inventory->lat.','.(float) $inventory->lng;
    }

    public function normalizeRef(?string $raw): ?string
    {
        $raw = strtoupper(trim((string) $raw));
        if ($raw === '') {
            return null;
        }
        $raw = preg_replace('/\s+/', '', $raw) ?? $raw;
        $raw = str_replace(['-', '.'], '', $raw);
        if (preg_match('/^(D|O|K)(\d+)/', $raw, $m)) {
            return $m[1].$m[2];
        }

        return $raw;
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earth * asin(min(1, sqrt($a)));
    }
}
