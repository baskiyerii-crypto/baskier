<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Address;
use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use App\Models\TurkiyeMahalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GeographyController extends ApiController
{
    public function countries()
    {
        $rows = \App\Models\Country::catalog()->map(fn ($c) => [
            'code' => $c->code,
            'name' => $c->localizedName(),
        ])->values();

        return $this->ok($rows);
    }

    public function places(Request $request, \App\Services\WorldPlaceService $places)
    {
        $code = strtoupper(trim((string) $request->query('country', '')));
        if (! \App\Support\IsoCountries::isValid($code)) {
            return $this->fail('Geçersiz ülke kodu.', null, 422);
        }
        $city = trim((string) $request->query('city', ''));

        return $this->ok([
            'cities' => $places->cities($code),
            'districts' => $city === '' ? [] : $places->districts($code, $city),
        ]);
    }

    public function provinces()
    {
        $rows = TurkiyeIl::query()->orderBy('name')->get(['id', 'name']);
        if ($rows->isEmpty()) {
            $rows = collect($this->fallbackProvincesFromFile());
        }

        return $this->ok($rows);
    }

    public function districts(int $provinceId)
    {
        $rows = TurkiyeIlce::query()
            ->where('province_id', $provinceId)
            ->orderBy('name')
            ->get(['id', 'name', 'postal_code']);
        if ($rows->isEmpty()) {
            $rows = collect($this->fallbackDistrictsFromFile($provinceId));
        }

        return $this->ok($rows);
    }

    public function neighborhoods(int $districtId)
    {
        $rows = TurkiyeMahalle::query()
            ->where('district_id', $districtId)
            ->orderBy('name')
            ->get(['id', 'name']);
        if ($rows->isEmpty()) {
            $rows = collect($this->fallbackNeighborhoodsFromFile($districtId));
        }

        return $this->ok($rows);
    }

    public function postalLookup(Request $request)
    {
        $code = preg_replace('/\D/', '', (string) $request->query('code', ''));
        if (strlen($code) !== 5) {
            return $this->fail('Posta kodu 5 haneli olmalıdır.', null, 422);
        }

        $matches = TurkiyeIlce::query()
            ->where('postal_code', $code)
            ->with('il:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (TurkiyeIlce $d) => [
                'district_id' => $d->id,
                'district_name' => $d->name,
                'province_id' => $d->province_id,
                'province_name' => $d->il?->name,
                'postal_code' => $d->postal_code,
            ])
            ->values()
            ->all();

        $fallback = false;
        if ($matches === []) {
            $matches = $this->lookupFromSavedNeighborhoodAddresses($code);
            $fallback = $matches !== [];
        }
        if ($matches === []) {
            // District-level postal data is incomplete for some 5-digit codes.
            // Fallback: first two digits map to province plate code.
            $provinceId = (int) substr($code, 0, 2);
            $province = TurkiyeIl::query()->find($provinceId, ['id', 'name']);
            if ($province) {
                $matches = TurkiyeIlce::query()
                    ->where('province_id', $provinceId)
                    ->with('il:id,name')
                    ->orderBy('name')
                    ->limit(60)
                    ->get()
                    ->map(fn (TurkiyeIlce $d) => [
                        'district_id' => $d->id,
                        'district_name' => $d->name,
                        'province_id' => $d->province_id,
                        'province_name' => $d->il?->name,
                        'postal_code' => $d->postal_code,
                    ])
                    ->values()
                    ->all();
                $fallback = $matches !== [];
            }
        }

        return $this->ok([
            'postal_code' => $code,
            'matches' => $matches,
            'fallback' => $fallback,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function lookupFromSavedNeighborhoodAddresses(string $code): array
    {
        return Address::query()
            ->join('turkiye_ilceler', 'turkiye_ilceler.id', '=', 'addresses.turkiye_district_id')
            ->join('turkiye_iller', 'turkiye_iller.id', '=', 'turkiye_ilceler.province_id')
            ->select([
                'addresses.turkiye_district_id',
                'turkiye_ilceler.name as district_name',
                'turkiye_iller.id as province_id',
                'turkiye_iller.name as province_name',
                'addresses.postal_code',
            ])
            ->where('addresses.postal_code', $code)
            ->whereNotNull('addresses.turkiye_district_id')
            ->distinct()
            ->limit(60)
            ->get()
            ->map(function ($row) {
                return [
                    'district_id' => (int) $row->turkiye_district_id,
                    'district_name' => (string) $row->district_name,
                    'province_id' => (int) $row->province_id,
                    'province_name' => (string) $row->province_name,
                    'postal_code' => (string) $row->postal_code,
                ];
            })
            ->values()
            ->all();
    }

    private function fallbackProvincesFromFile(): array
    {
        $json = $this->readJsonFile(database_path('data/provinces.json'));
        $rows = $json['data'] ?? [];
        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row) => isset($row['id'], $row['name']))
            ->map(fn ($row) => [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function fallbackDistrictsFromFile(int $provinceId): array
    {
        $rows = $this->readJsonFile(database_path('data/districts.json'));
        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row) => isset($row['id'], $row['name'], $row['provinceId']) && (int) $row['provinceId'] === $provinceId)
            ->map(function ($row) {
                $postal = preg_replace('/\D/', '', (string) ($row['postalCode'] ?? ''));

                return [
                    'id' => (int) $row['id'],
                    'name' => (string) $row['name'],
                    'postal_code' => strlen($postal) === 5 ? $postal : null,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function fallbackNeighborhoodsFromFile(int $districtId): array
    {
        $rows = $this->readJsonFile(database_path('data/neighborhoods.json'));
        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row) => isset($row['id'], $row['name'], $row['districtId']) && (int) $row['districtId'] === $districtId)
            ->map(fn ($row) => [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function readJsonFile(string $path): mixed
    {
        if (! File::exists($path)) {
            return null;
        }

        return json_decode((string) File::get($path), true);
    }
}
