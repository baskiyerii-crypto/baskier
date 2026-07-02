<?php

namespace Database\Seeders;

use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use App\Models\TurkiyeMahalle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurkiyeGeographySeeder extends Seeder
{
    private const API = 'https://api.turkiyeapi.dev/v1';

    public function run(): void
    {
        $fromFiles = $this->seedFromJsonFiles();
        if (! $fromFiles) {
            $this->seedFromHttp();
        }
    }

    private function seedFromJsonFiles(): bool
    {
        $pPath = database_path('data/provinces.json');
        $dPath = database_path('data/districts.json');
        if (! is_file($pPath) || ! is_file($dPath)) {
            return false;
        }
        $pJson = json_decode((string) file_get_contents($pPath), true);
        $provinces = $pJson['data'] ?? null;
        if (! is_array($provinces)) {
            return false;
        }
        foreach ($provinces as $row) {
            if (! isset($row['id'], $row['name'])) {
                continue;
            }
            TurkiyeIl::query()->updateOrCreate(
                ['id' => (int) $row['id']],
                ['name' => (string) $row['name']],
            );
        }
        $districts = json_decode((string) file_get_contents($dPath), true);
        if (! is_array($districts)) {
            return false;
        }
        foreach ($districts as $row) {
            if (! isset($row['id'], $row['name'], $row['provinceId'], $row['postalCode'])) {
                continue;
            }
            $code = preg_replace('/\D/', '', (string) $row['postalCode']);
            if (strlen($code) !== 5) {
                continue;
            }
            TurkiyeIlce::query()->updateOrCreate(
                ['id' => (int) $row['id']],
                [
                    'province_id' => (int) $row['provinceId'],
                    'name' => (string) $row['name'],
                    'postal_code' => $code,
                ],
            );
        }

        $this->seedNeighborhoodsFromFile();

        return TurkiyeIl::query()->exists();
    }

    private function seedNeighborhoodsFromFile(): void
    {
        $path = database_path('data/neighborhoods.json');
        if (! is_file($path)) {
            return;
        }
        $rows = json_decode((string) file_get_contents($path), true);
        if (! is_array($rows)) {
            return;
        }
        $now = now();
        foreach (array_chunk($rows, 500) as $chunk) {
            $payload = [];
            foreach ($chunk as $row) {
                if (! isset($row['id'], $row['name'], $row['districtId'], $row['provinceId'])) {
                    continue;
                }
                $payload[] = [
                    'id' => (int) $row['id'],
                    'district_id' => (int) $row['districtId'],
                    'province_id' => (int) $row['provinceId'],
                    'name' => (string) $row['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($payload === []) {
                continue;
            }
            TurkiyeMahalle::query()->upsert(
                $payload,
                ['id'],
                ['district_id', 'province_id', 'name', 'updated_at'],
            );
        }
    }

    private function seedFromHttp(): void
    {
        try {
            $this->seedProvincesHttp();
            $this->seedDistrictsHttp();
            $this->seedNeighborhoodsHttp();
        } catch (\Throwable $e) {
            Log::warning('TurkiyeGeographySeeder HTTP: '.$e->getMessage());
        }
    }

    private function seedProvincesHttp(): void
    {
        $res = Http::timeout(60)
            ->withOptions(['verify' => (bool) config('turkiye_geography.verify_ssl', true)])
            ->get(self::API.'/provinces', ['limit' => 100, 'fields' => 'id,name']);
        if (! $res->successful()) {
            return;
        }
        $rows = $res->json('data');
        if (! is_array($rows)) {
            return;
        }
        foreach ($rows as $row) {
            if (! isset($row['id'], $row['name'])) {
                continue;
            }
            TurkiyeIl::query()->updateOrCreate(
                ['id' => (int) $row['id']],
                ['name' => (string) $row['name']],
            );
        }
    }

    private function seedDistrictsHttp(): void
    {
        $offset = 0;
        $limit = 100;
        do {
            $res = Http::timeout(120)
                ->withOptions(['verify' => (bool) config('turkiye_geography.verify_ssl', true)])
                ->get(self::API.'/districts', [
                    'limit' => $limit,
                    'offset' => $offset,
                    'activatePostalCodes' => 'true',
                    'fields' => 'id,name,provinceId,postalCode',
                ]);
            if (! $res->successful()) {
                break;
            }
            $rows = $res->json('data');
            if (! is_array($rows) || $rows === []) {
                break;
            }
            foreach ($rows as $row) {
                if (! isset($row['id'], $row['name'], $row['provinceId'], $row['postalCode'])) {
                    continue;
                }
                $code = preg_replace('/\D/', '', (string) $row['postalCode']);
                if (strlen($code) !== 5) {
                    continue;
                }
                TurkiyeIlce::query()->updateOrCreate(
                    ['id' => (int) $row['id']],
                    [
                        'province_id' => (int) $row['provinceId'],
                        'name' => (string) $row['name'],
                        'postal_code' => $code,
                    ],
                );
            }
            $offset += $limit;
        } while (count($rows) === $limit);
    }

    private function seedNeighborhoodsHttp(): void
    {
        $offset = 0;
        $limit = 500;
        $now = now();
        $batchCount = 0;
        do {
            $res = Http::timeout(120)
                ->withOptions(['verify' => (bool) config('turkiye_geography.verify_ssl', true)])
                ->get(self::API.'/neighborhoods', [
                    'limit' => $limit,
                    'offset' => $offset,
                    'fields' => 'id,name,districtId,provinceId',
                ]);
            if (! $res->successful()) {
                break;
            }
            $rows = $res->json('data');
            if (! is_array($rows) || $rows === []) {
                break;
            }
            $payload = [];
            foreach ($rows as $row) {
                if (! isset($row['id'], $row['name'], $row['districtId'], $row['provinceId'])) {
                    continue;
                }
                $payload[] = [
                    'id' => (int) $row['id'],
                    'district_id' => (int) $row['districtId'],
                    'province_id' => (int) $row['provinceId'],
                    'name' => (string) $row['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($payload !== []) {
                TurkiyeMahalle::query()->upsert(
                    $payload,
                    ['id'],
                    ['district_id', 'province_id', 'name', 'updated_at'],
                );
            }
            $batchCount = count($rows);
            $offset += $limit;
        } while ($batchCount === $limit);
    }
}
