@extends('layouts.vendor')
@section('title', __('panel.profile'))
@section('content')
@if($vendor->profile_pending_payload)
    <div class="alert alert-warning small">{{ __('panel.profile_pending_notice') }}</div>
@endif
<form method="post" action="{{ route('vendor.profile.update') }}" enctype="multipart/form-data" class="card border-0 shadow-sm p-4">
    @csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">{{ __('panel.name') }}</label><input name="name" class="form-control" value="{{ old('name', $vendor->name) }}" required></div>
        <div class="col-md-6"><label class="form-label">{{ __('panel.email') }}</label><input name="email" type="email" class="form-control" value="{{ old('email', $vendor->email) }}"></div>
        <div class="col-md-6"><label class="form-label">{{ __('panel.phone') }}</label><input name="phone" class="form-control" value="{{ old('phone', $vendor->phone) }}"></div>
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
        <div class="col-12"><label class="form-label">{{ __('panel.address') }}</label><input name="address" class="form-control" value="{{ old('address', $vendor->address) }}"></div>
        <div class="col-12"><label class="form-label">{{ __('panel.description') }}</label><textarea name="description" class="form-control" rows="4">{{ old('description', $vendor->description) }}</textarea></div>
        <div class="col-md-6"><label class="form-label">{{ __('panel.map_embed') }}</label><input name="map_embed_url" class="form-control" value="{{ old('map_embed_url', $vendor->map_embed_url) }}"></div>
        <div class="col-md-3"><label class="form-label">Lat</label><input name="map_lat" class="form-control" value="{{ old('map_lat', $vendor->map_lat) }}"></div>
        <div class="col-md-3"><label class="form-label">Lng</label><input name="map_lng" class="form-control" value="{{ old('map_lng', $vendor->map_lng) }}"></div>
        <div class="col-md-6"><label class="form-label">Instagram</label><input name="social_instagram" class="form-control" value="{{ old('social_instagram', $vendor->social_links['instagram'] ?? '') }}"></div>
        <div class="col-md-6"><label class="form-label">Website</label><input name="social_website" class="form-control" value="{{ old('social_website', $vendor->social_links['website'] ?? '') }}"></div>
        <div class="col-md-6"><label class="form-label">Logo</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
        <div class="col-md-6"><label class="form-label">Kapak (duvar) görseli</label><input type="file" name="cover_image" class="form-control" accept="image/*"></div>
    </div>
    <p class="small text-muted mt-3">{{ __('panel.profile_approval_help') }}</p>
    <button class="btn btn-primary mt-2">{{ __('panel.submit_for_approval') }}</button>
</form>
@endsection
