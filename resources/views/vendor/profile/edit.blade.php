@extends('layouts.vendor')
@section('title', __('panel.profile'))
@section('content')
@if($vendor->profile_pending_payload)
    <div class="alert alert-warning small d-flex align-items-center gap-2">
        <span>⚠️</span>
        <div>{{ __('panel.profile_pending_notice') }}</div>
    </div>
@endif

<form method="post" action="{{ route('vendor.profile.update') }}" enctype="multipart/form-data" class="card border-0 shadow-sm p-4">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">{{ __('panel.name') }} <span class="text-danger">*</span></label>
            <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $vendor->name) }}" required>
            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">{{ __('panel.email') }}</label>
            <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $vendor->email) }}">
            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">{{ __('panel.phone') }}</label>
            <input name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $vendor->phone) }}" placeholder="05XXXXXXXXX">
            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            @include('partials.geo-location-fields', [
                'countryValue' => $vendor->country_code ?: 'TR',
                'cityValue' => $vendor->city,
                'districtValue' => $vendor->district,
                'countries' => $countries ?? collect(),
                'provinces' => $provinces ?? collect(),
                'trDistricts' => $districts ?? collect(),
                'citySuggestions' => $citySuggestions ?? [],
                'districtSuggestions' => $districtSuggestions ?? [],
                'idPrefix' => 'vendor-geo',
            ])
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold small">{{ __('panel.address') }}</label>
            <input name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $vendor->address) }}">
            @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold small">{{ __('panel.description') }}</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $vendor->description) }}</textarea>
            @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">{{ __('panel.map_embed') }}</label>
            <input name="map_embed_url" class="form-control @error('map_embed_url') is-invalid @enderror" value="{{ old('map_embed_url', $vendor->map_embed_url) }}" placeholder="Google Maps embed iframe/link">
            @error('map_embed_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold small">Harita Enlem (Lat)</label>
            <input type="number" step="any" inputmode="decimal" name="map_lat" class="form-control number-only-input @error('map_lat') is-invalid @enderror" value="{{ old('map_lat', $vendor->map_lat) }}" placeholder="Örn: 41.0082">
            @error('map_lat')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold small">Harita Boylam (Lng)</label>
            <input type="number" step="any" inputmode="decimal" name="map_lng" class="form-control number-only-input @error('map_lng') is-invalid @enderror" value="{{ old('map_lng', $vendor->map_lng) }}" placeholder="Örn: 28.9784">
            @error('map_lng')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Instagram</label>
            <input name="social_instagram" class="form-control @error('social_instagram') is-invalid @enderror" value="{{ old('social_instagram', $vendor->social_links['instagram'] ?? '') }}" placeholder="kullaniciadi veya profil linki">
            @error('social_instagram')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Website</label>
            <input name="social_website" class="form-control @error('social_website') is-invalid @enderror" value="{{ old('social_website', $vendor->social_links['website'] ?? '') }}" placeholder="https://ornek.com">
            <div class="form-text text-muted small">Örn: https://firmaniz.com</div>
            @error('social_website')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Logo</label>
            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
            @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Kapak (duvar) görseli</label>
            <input type="file" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" accept="image/*">
            @error('cover_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>
    <p class="small text-muted mt-3">{{ __('panel.profile_approval_help') }}</p>
    <div>
        <button type="submit" class="btn btn-primary mt-2">{{ __('panel.submit_for_approval') }}</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.number-only-input').forEach(function(input) {
        input.addEventListener('keydown', function(e) {
            // İzin verilen tuşlar: rakamlar, nokta, eksi, Backspace, Delete, Tab, ok tuşları
            const allowedKeys = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', '.', ',', '-'];
            if (allowedKeys.includes(e.key)) return;
            if (e.ctrlKey || e.metaKey) return;
            if (!/^[0-9]$/.test(e.key)) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection
