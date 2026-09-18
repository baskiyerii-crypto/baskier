<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use App\Models\VendorProfileChangeRequest;
use App\Support\IsoCountries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class VendorProfileController extends Controller
{
    public function edit(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        $countries = Country::catalog();
        $provinces = Schema::hasTable('turkiye_iller') ? TurkiyeIl::query()->orderBy('name')->get() : collect();
        $provinceId = old('turkiye_il_id');
        $districts = ($provinceId && Schema::hasTable('turkiye_ilceler'))
            ? TurkiyeIlce::query()->where('province_id', $provinceId)->orderBy('name')->get()
            : collect();
        $places = app(\App\Services\WorldPlaceService::class);
        $countryCode = old('country_code', $vendor->country_code ?: 'TR');
        $citySuggestions = $places->cities((string) $countryCode);
        $districtSuggestions = $places->districts((string) $countryCode, (string) old('city', $vendor->city));

        return view('vendor.profile.edit', compact(
            'vendor',
            'countries',
            'provinces',
            'districts',
            'citySuggestions',
            'districtSuggestions'
        ));
    }

    public function update(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        if ($website = $request->input('social_website')) {
            $website = trim((string) $website);
            if ($website !== '' && ! preg_match('~^https?://~i', $website)) {
                $request->merge(['social_website' => 'https://'.$website]);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2', 'in:'.implode(',', IsoCountries::codes())],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'turkiye_il_id' => ['nullable', 'integer'],
            'turkiye_ilce_id' => ['nullable', 'integer'],
            'address' => ['nullable', 'string', 'max:500'],
            'map_embed_url' => ['nullable', 'string', 'max:500'],
            'map_lat' => ['nullable', 'numeric'],
            'map_lng' => ['nullable', 'numeric'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_website' => ['nullable', 'url', 'max:255'],
        ], [
            'social_website.url' => 'Web sitesi adresi geçerli bir URL olmalıdır (örn: https://siteniz.com).',
            'map_lat.numeric' => 'Harita enlem (Lat) sadece rakam/ondalık sayı olabilir.',
            'map_lng.numeric' => 'Harita boylam (Lng) sadece rakam/ondalık sayı olabilir.',
            'name.required' => 'Mağaza / satıcı adı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
        ]);

        $payload = collect($validated)->except(['logo', 'cover_image', 'social_instagram', 'social_website', 'turkiye_il_id', 'turkiye_ilce_id'])->all();
        $geo = app(\App\Services\WorldPlaceService::class)->normalize(
            $validated['country_code'] ?? $vendor->country_code,
            $validated['city'] ?? null,
            $validated['district'] ?? null,
            $validated['turkiye_il_id'] ?? null,
            $validated['turkiye_ilce_id'] ?? null,
        );
        $payload['country_code'] = $geo['country_code'];
        $payload['city'] = $geo['city'];
        $payload['district'] = $geo['district'];
        $payload['social_links'] = array_filter([
            'instagram' => $validated['social_instagram'] ?? null,
            'website' => $validated['social_website'] ?? null,
        ]);

        if ($request->hasFile('logo')) {
            $payload['logo'] = $request->file('logo')->store('vendor-logos/'.$vendor->id, 'public');
        }
        if ($request->hasFile('cover_image')) {
            $payload['cover_image'] = $request->file('cover_image')->store('vendor-covers/'.$vendor->id, 'public');
        }

        $vendor->update(['profile_pending_payload' => $payload]);
        VendorProfileChangeRequest::create([
            'vendor_id' => $vendor->id,
            'payload' => $payload,
            'status' => 'pending',
        ]);

        return back()->with('success', __('panel.profile_change_submitted'));
    }
}
