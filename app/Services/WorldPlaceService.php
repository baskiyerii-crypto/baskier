<?php

namespace App\Services;

use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use App\Models\WorldPlace;
use App\Support\IsoCountries;
use Illuminate\Support\Facades\Schema;

class WorldPlaceService
{
    /**
     * @return array{country_code: string, city: ?string, district: ?string, turkiye_il_id: mixed, turkiye_ilce_id: mixed}
     */
    public function normalize(?string $countryCode, ?string $city, ?string $district = null, mixed $ilId = null, mixed $ilceId = null): array
    {
        $code = strtoupper(trim((string) $countryCode));
        if (! IsoCountries::isValid($code)) {
            $code = 'TR';
        }
        $city = trim((string) $city);
        $district = trim((string) $district);
        $ilId = $code === 'TR' ? ($ilId ?: null) : null;
        $ilceId = $code === 'TR' ? ($ilceId ?: null) : null;

        if ($code === 'TR' && $city === '' && $ilId && Schema::hasTable('turkiye_iller')) {
            $city = trim((string) (TurkiyeIl::query()->find($ilId)?->name ?? ''));
        }
        if ($code === 'TR' && $district === '' && $ilceId && Schema::hasTable('turkiye_ilceler')) {
            $district = trim((string) (TurkiyeIlce::query()->find($ilceId)?->name ?? ''));
        }

        $this->remember($code, $city, $district);

        return [
            'country_code' => $code,
            'city' => $city !== '' ? mb_substr($city, 0, 120) : null,
            'district' => $district !== '' ? mb_substr($district, 0, 120) : null,
            'turkiye_il_id' => $ilId,
            'turkiye_ilce_id' => $ilceId,
        ];
    }

    public function remember(?string $countryCode, ?string $city, ?string $district = null): void
    {
        $countryCode = strtoupper(trim((string) $countryCode));
        $city = trim((string) $city);
        $district = trim((string) $district);
        if (! IsoCountries::isValid($countryCode) || $city === '' || ! Schema::hasTable('world_places')) {
            return;
        }

        WorldPlace::query()->firstOrCreate([
            'country_code' => $countryCode,
            'city' => mb_substr($city, 0, 120),
            'district' => mb_substr($district, 0, 120),
        ]);
    }

    /**
     * @return list<string>
     */
    public function cities(string $countryCode): array
    {
        if (! Schema::hasTable('world_places')) {
            return [];
        }

        return WorldPlace::query()
            ->where('country_code', strtoupper($countryCode))
            ->orderBy('city')
            ->pluck('city')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function districts(string $countryCode, string $city): array
    {
        if (! Schema::hasTable('world_places')) {
            return [];
        }

        return WorldPlace::query()
            ->where('country_code', strtoupper($countryCode))
            ->where('city', $city)
            ->where('district', '!=', '')
            ->orderBy('district')
            ->pluck('district')
            ->unique()
            ->values()
            ->all();
    }
}
