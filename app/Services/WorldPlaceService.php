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

        if ($code === 'TR' && ! $ilId && $city !== '' && Schema::hasTable('turkiye_iller')) {
            $ilId = TurkiyeIl::query()->where('name', $city)->value('id');
        }
        if ($code === 'TR' && $city === '' && $ilId && Schema::hasTable('turkiye_iller')) {
            $city = trim((string) (TurkiyeIl::query()->find($ilId)?->name ?? ''));
        }
        if ($code === 'TR' && ! $ilceId && $district !== '' && Schema::hasTable('turkiye_ilceler')) {
            $ilceQuery = TurkiyeIlce::query()->where('name', $district);
            if ($ilId) {
                $ilceQuery->where('province_id', $ilId);
            }
            $ilceId = $ilceQuery->value('id');
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
        $code = strtoupper($countryCode);
        $names = collect();
        if (Schema::hasTable('world_places')) {
            $names = $names->merge(
                WorldPlace::query()->where('country_code', $code)->orderBy('city')->pluck('city')
            );
        }
        if (Schema::hasTable('ooh_inventories') && Schema::hasColumn('ooh_inventories', 'country_code')) {
            $names = $names->merge(
                \App\Models\OohInventory::query()
                    ->where('country_code', $code)
                    ->whereNotNull('city')
                    ->where('city', '!=', '')
                    ->pluck('city')
            );
        }
        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'country_code')) {
            $names = $names->merge(
                \App\Models\Vendor::query()
                    ->where('country_code', $code)
                    ->whereNotNull('city')
                    ->where('city', '!=', '')
                    ->pluck('city')
            );
        }

        return $names
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function districts(string $countryCode, string $city): array
    {
        $code = strtoupper($countryCode);
        $city = trim($city);
        $names = collect();
        if (Schema::hasTable('world_places')) {
            $names = $names->merge(
                WorldPlace::query()
                    ->where('country_code', $code)
                    ->where('city', $city)
                    ->where('district', '!=', '')
                    ->orderBy('district')
                    ->pluck('district')
            );
        }
        if (Schema::hasTable('ooh_inventories') && Schema::hasColumn('ooh_inventories', 'country_code')) {
            $names = $names->merge(
                \App\Models\OohInventory::query()
                    ->where('country_code', $code)
                    ->where('city', $city)
                    ->whereNotNull('district')
                    ->where('district', '!=', '')
                    ->pluck('district')
            );
        }
        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'country_code')) {
            $names = $names->merge(
                \App\Models\Vendor::query()
                    ->where('country_code', $code)
                    ->where('city', $city)
                    ->whereNotNull('district')
                    ->where('district', '!=', '')
                    ->pluck('district')
            );
        }

        return $names
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
