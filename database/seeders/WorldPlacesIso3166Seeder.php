<?php

namespace Database\Seeders;

use App\Models\WorldPlace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class WorldPlacesIso3166Seeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('world_places')) {
            return;
        }

        $path = database_path('data/iso3166-2-subdivisions.json');
        if (! File::exists($path)) {
            return;
        }

        $data = json_decode((string) File::get($path), true);
        if (! is_array($data)) {
            return;
        }

        foreach ($data as $code => $cities) {
            $code = strtoupper((string) $code);
            if ($code === 'TR' || ! is_array($cities)) {
                continue;
            }
            foreach ($cities as $city) {
                $city = trim((string) $city);
                if ($city === '') {
                    continue;
                }
                WorldPlace::query()->firstOrCreate([
                    'country_code' => $code,
                    'city' => mb_substr($city, 0, 120),
                    'district' => '',
                ]);
            }
        }
    }
}
