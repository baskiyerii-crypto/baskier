@extends('layouts.vendor')
@section('title', isset($inventory) ? 'Pano düzenle' : 'Yeni pano')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if(!empty($similar) && $similar->isNotEmpty())
    <div class="alert alert-warning">Aynı ruhsat/konumda başka ilan: {{ $similar->pluck('title')->implode(', ') }}. Çift ilan raporu açabilir veya yönetici onayına bırakabilirsiniz.</div>
@endif
<div class="card p-4" style="max-width:720px;">
    <form method="POST" enctype="multipart/form-data" action="{{ isset($inventory) ? route('vendor.outdoor.inventories.update', $inventory) : route('vendor.outdoor.inventories.store') }}">
        @csrf
        @if(isset($inventory)) @method('PUT') @endif
        <div class="mb-3"><label class="form-label">Başlık</label><input name="title" class="form-control" value="{{ old('title', $inventory->title ?? '') }}" required></div>
        @if($categories->isEmpty())
            <div class="alert alert-warning small d-flex align-items-center gap-2 mb-3">
                <span>⚠️</span>
                <div>Sistemde henüz açık hava kategorisi bulunmuyor. Pano ekleyebilmek için lütfen yönetici ile iletişime geçiniz.</div>
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label fw-semibold small">Kategori <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select" required @disabled($categories->isEmpty())>
                @forelse($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id', $inventory->category_id ?? '') == $c->id)>{{ $c->name }}</option>
                @empty
                    <option value="" disabled selected>Kategori tanımlanmamış (Yönetici eklemelidir)</option>
                @endforelse
            </select>
        </div>
        <div class="mb-3"><label class="form-label fw-semibold small">Açıklama</label><textarea name="description" class="form-control" rows="3">{{ old('description', $inventory->description ?? '') }}</textarea></div>
        @include('partials.geo-location-fields', [
            'countryValue' => $inventory?->country_code ?? 'TR',
            'cityValue' => $inventory?->city ?? '',
            'districtValue' => $inventory?->district ?? '',
            'ilValue' => $inventory?->turkiye_il_id ?? '',
            'ilceValue' => $inventory?->turkiye_ilce_id ?? '',
            'countries' => $countries ?? collect(),
            'provinces' => $provinces,
            'trDistricts' => $districts ?? collect(),
            'citySuggestions' => $citySuggestions ?? [],
            'districtSuggestions' => $districtSuggestions ?? [],
            'idPrefix' => 'ooh-inv',
            'emptyCountry' => false,
        ])
        <div class="mb-3 mt-3"><label class="form-label fw-semibold small">Adres</label><input name="address" class="form-control" value="{{ old('address', $inventory->address ?? '') }}"></div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold small">Enlem (Lat) <span class="text-danger">*</span></label>
                <input type="number" step="any" inputmode="decimal" name="lat" class="form-control number-only-input" value="{{ old('lat', $inventory->lat ?? '') }}" required placeholder="Örn: 41.0082">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold small">Boylam (Lng) <span class="text-danger">*</span></label>
                <input type="number" step="any" inputmode="decimal" name="lng" class="form-control number-only-input" value="{{ old('lng', $inventory->lng ?? '') }}" required placeholder="Örn: 28.9784">
            </div>
        </div>
        <div class="mb-3"><label class="form-label fw-semibold small">Ruhsat no</label><input name="permit_no" class="form-control" value="{{ old('permit_no', $inventory->permit_no ?? '') }}"></div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold small">Liste fiyatı (₺)</label>
                <input name="list_price" type="number" step="0.01" min="0" class="form-control number-only-input" value="{{ old('list_price', $inventory->list_price ?? '') }}" placeholder="0.00">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold small">Fiyat Birimi</label>
                <select name="price_unit" class="form-select">
                    @foreach(['day'=>'Gün','week'=>'Hafta','month'=>'Ay'] as $k=>$lab)
                        <option value="{{ $k }}" @selected(old('price_unit', $inventory->price_unit ?? 'month')===$k)>{{ $lab }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold small">GPS yarıçap (m)</label>
            <input name="proof_radius_m" type="number" min="1" step="1" class="form-control number-only-input" value="{{ old('proof_radius_m', $inventory->proof_radius_m ?? 75) }}">
        </div>
        <div class="mb-3"><label class="form-label fw-semibold small">Fotoğraflar</label><input type="file" name="images[]" class="form-control" accept="image/*" multiple></div>
        <button class="btn btn-success" @disabled($categories->isEmpty())>Kaydet</button>
    </form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.number-only-input').forEach(function(input) {
        input.addEventListener('keydown', function(e) {
            const allowed = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', '.', ',', '-', 'Enter'];
            if (allowed.includes(e.key)) return;
            if (e.ctrlKey || e.metaKey) return;
            if (!/^[0-9]$/.test(e.key)) {
                e.preventDefault();
            }
        });
    });
});
</script>
    @if(isset($inventory))
        <form method="POST" action="{{ route('vendor.outdoor.inventories.submit', $inventory) }}" class="mt-3">@csrf<button class="btn btn-outline-primary btn-sm">İncelemeye gönder</button></form>
        <form method="POST" action="{{ route('vendor.outdoor.inventories.block', $inventory) }}" class="mt-3 d-flex gap-2">
            @csrf
            <input type="date" name="starts_on" class="form-control" required>
            <input type="date" name="ends_on" class="form-control" required>
            <button class="btn btn-outline-secondary">Bloke et</button>
        </form>
        @if(!empty($calendar))
            <p class="small text-muted mt-3">Takvim</p>
            @foreach($calendar as $row)<div class="small">{{ $row['starts_on'] }} – {{ $row['ends_on'] }} · {{ $row['kind'] }}</div>@endforeach
        @endif
        @if($inventory->status === 'rejected')<p class="text-danger small mt-2">Red: {{ $inventory->rejection_reason }}</p>@endif
    @endif
</div>
@endsection
