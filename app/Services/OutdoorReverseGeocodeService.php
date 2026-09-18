<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use App\Support\IsoCountries;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OutdoorReverseGeocodeService
{
    /**
     * @return array{
     *   country_code: ?string,
     *   city: ?string,
     *   district: ?string,
     *   turkiye_il_id: ?int,
     *   turkiye_ilce_id: ?int
     * }
     */
    public function lookup(float $lat, float $lng): array
    {
        $empty = [
            'country_code' => null,
            'city' => null,
            'district' => null,
            'turkiye_il_id' => null,
            'turkiye_ilce_id' => null,
        ];

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return $empty;
        }

        $parsed = $this->fromNominatim($lat, $lng);
        if ($parsed === null || ($parsed['country_code'] === null && $parsed['city'] === null)) {
            $parsed = $this->fromGoogle($lat, $lng) ?? $parsed;
        }

        if ($parsed === null) {
            return $empty;
        }

        return $this->resolveTurkeyIds($parsed);
    }

    /**
     * @param  array{country_code:?string,city:?string,district:?string}  $geo
     * @return array{country_code:?string,city:?string,district:?string,turkiye_il_id:?int,turkiye_ilce_id:?int}
     */
    private function resolveTurkeyIds(array $geo): array
    {
        $code = $geo['country_code'] ? strtoupper($geo['country_code']) : null;
        $city = $geo['city'] ? trim($geo['city']) : null;
        $district = $geo['district'] ? trim($geo['district']) : null;
        $ilId = null;
        $ilceId = null;

        if ($code === 'TR' && Schema::hasTable('turkiye_iller') && $city) {
            $il = TurkiyeIl::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($city)])
                ->first();
            if (! $il) {
                $il = TurkiyeIl::query()->where('name', 'like', $city.'%')->orderBy('name')->first();
            }
            $ilId = $il?->id;
            if ($ilId && $district && Schema::hasTable('turkiye_ilceler')) {
                $ilce = TurkiyeIlce::query()
                    ->where('province_id', $ilId)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($district)])
                    ->first();
                if (! $ilce) {
                    $ilce = TurkiyeIlce::query()
                        ->where('province_id', $ilId)
                        ->where('name', 'like', $district.'%')
                        ->orderBy('name')
                        ->first();
                }
                $ilceId = $ilce?->id;
                if ($ilce) {
                    $district = $ilce->name;
                }
            }
            if ($il) {
                $city = $il->name;
            }
        }

        return [
            'country_code' => $code && IsoCountries::isValid($code) ? $code : null,
            'city' => $city !== '' && $city !== null ? mb_substr($city, 0, 120) : null,
            'district' => $district !== '' && $district !== null ? mb_substr($district, 0, 120) : null,
            'turkiye_il_id' => $ilId,
            'turkiye_ilce_id' => $ilceId,
        ];
    }

    /**
     * @return array{country_code:?string,city:?string,district:?string}|null
     */
    private function fromNominatim(float $lat, float $lng): ?array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'BaskiYeriOutdoor/1.0 (outdoor-inventory)',
                    'Accept-Language' => 'tr',
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => number_format($lat, 7, '.', ''),
                    'lon' => number_format($lng, 7, '.', ''),
                    'addressdetails' => 1,
                ]);
            if (! $response->successful()) {
                return null;
            }
            $address = $response->json('address');
            if (! is_array($address)) {
                return null;
            }

            return $this->mapAddressParts($address);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array{country_code:?string,city:?string,district:?string}|null
     */
    private function fromGoogle(float $lat, float $lng): ?array
    {
        if (! Setting::apiEnabled('google', false)) {
            return null;
        }
        $key = Setting::get('google_maps_api_key', '');
        if ($key === '') {
            return null;
        }

        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => $lat.','.$lng,
                'key' => $key,
                'language' => 'tr',
            ]);
            if (! $response->successful() || $response->json('status') !== 'OK') {
                return null;
            }
            $components = $response->json('results.0.address_components');
            if (! is_array($components)) {
                return null;
            }
            $parts = [];
            foreach ($components as $c) {
                $types = $c['types'] ?? [];
                $name = $c['long_name'] ?? null;
                if (! $name) {
                    continue;
                }
                if (in_array('country', $types, true)) {
                    $parts['country_code'] = strtoupper((string) ($c['short_name'] ?? ''));
                }
                if (in_array('administrative_area_level_1', $types, true)) {
                    $parts['state'] = $name;
                }
                if (in_array('administrative_area_level_2', $types, true)) {
                    $parts['county'] = $name;
                }
                if (in_array('locality', $types, true) || in_array('postal_town', $types, true)) {
                    $parts['city'] = $name;
                }
                if (in_array('sublocality', $types, true) || in_array('sublocality_level_1', $types, true)) {
                    $parts['suburb'] = $name;
                }
            }

            $code = $parts['country_code'] ?? null;
            $city = $parts['state'] ?? $parts['city'] ?? null;
            $district = $parts['county'] ?? $parts['suburb'] ?? $parts['city'] ?? null;
            if ($code === 'TR') {
                $city = $parts['state'] ?? $parts['city'] ?? null;
                $district = $parts['county'] ?? $parts['suburb'] ?? null;
            }

            return [
                'country_code' => $code,
                'city' => $city,
                'district' => $district,
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $address
     * @return array{country_code:?string,city:?string,district:?string}
     */
    private function mapAddressParts(array $address): array
    {
        $code = isset($address['country_code']) ? strtoupper((string) $address['country_code']) : null;
        $city = $address['state']
            ?? $address['province']
            ?? $address['region']
            ?? $address['city']
            ?? $address['town']
            ?? $address['municipality']
            ?? null;
        $district = $address['county']
            ?? $address['city_district']
            ?? $address['suburb']
            ?? $address['district']
            ?? $address['town']
            ?? $address['village']
            ?? null;

        if ($code === 'TR') {
            $city = $address['province']
                ?? $address['state']
                ?? $address['city']
                ?? $city;
            $district = $address['town']
                ?? $address['county']
                ?? $address['city_district']
                ?? $address['suburb']
                ?? $address['district']
                ?? $district;
        }

        return [
            'country_code' => $code,
            'city' => is_string($city) ? $city : null,
            'district' => is_string($district) ? $district : null,
        ];
    }
}
