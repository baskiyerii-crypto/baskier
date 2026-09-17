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
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select" required>
                @forelse($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id', $inventory->category_id ?? '') == $c->id)>{{ $c->name }}</option>
                @empty
                    <option value="">Admin henüz outdoor kategorisi eklemedi</option>
                @endforelse
            </select>
        </div>
        <div class="mb-3"><label class="form-label">Açıklama</label><textarea name="description" class="form-control" rows="3">{{ old('description', $inventory->description ?? '') }}</textarea></div>
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">İl adı</label><input name="city" class="form-control" value="{{ old('city', $inventory->city ?? '') }}"></div>
            <div class="col-md-6 mb-3"><label class="form-label">İlçe adı</label><input name="district" class="form-control" value="{{ old('district', $inventory->district ?? '') }}"></div>
        </div>
        @if($provinces->isNotEmpty())
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">İl (kod)</label>
                    <select name="turkiye_il_id" id="ooh-il" class="form-select">
                        <option value="">—</option>
                        @foreach($provinces as $p)
                            <option value="{{ $p->id }}" @selected(old('turkiye_il_id', $inventory->turkiye_il_id ?? '') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">İlçe (kod)</label>
                    <select name="turkiye_ilce_id" id="ooh-ilce" class="form-select">
                        <option value="">—</option>
                        @foreach(($districts ?? collect()) as $d)
                            <option value="{{ $d->id }}" @selected(old('turkiye_ilce_id', $inventory->turkiye_ilce_id ?? '') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
        <div class="mb-3"><label class="form-label">Adres</label><input name="address" class="form-control" value="{{ old('address', $inventory->address ?? '') }}"></div>
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Lat</label><input name="lat" class="form-control" value="{{ old('lat', $inventory->lat ?? '') }}" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Lng</label><input name="lng" class="form-control" value="{{ old('lng', $inventory->lng ?? '') }}" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Ruhsat no</label><input name="permit_no" class="form-control" value="{{ old('permit_no', $inventory->permit_no ?? '') }}"></div>
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Liste fiyatı</label><input name="list_price" type="number" step="0.01" class="form-control" value="{{ old('list_price', $inventory->list_price ?? '') }}"></div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Birim</label>
                <select name="price_unit" class="form-select">
                    @foreach(['day'=>'Gün','week'=>'Hafta','month'=>'Ay'] as $k=>$lab)
                        <option value="{{ $k }}" @selected(old('price_unit', $inventory->price_unit ?? 'month')===$k)>{{ $lab }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mb-3"><label class="form-label">GPS yarıçap (m)</label><input name="proof_radius_m" type="number" class="form-control" value="{{ old('proof_radius_m', $inventory->proof_radius_m ?? 75) }}"></div>
        <div class="mb-3"><label class="form-label">Fotoğraflar</label><input type="file" name="images[]" class="form-control" accept="image/*" multiple></div>
        <button class="btn btn-success">Kaydet</button>
    </form>
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
@push('scripts')
<script>
(() => {
    const il = document.getElementById('ooh-il');
    const ilce = document.getElementById('ooh-ilce');
    if (!il || !ilce) return;
    const selected = ilce.value;
    const load = async () => {
        const id = il.value;
        ilce.innerHTML = '<option value="">—</option>';
        if (!id) return;
        try {
            const res = await fetch('/api/v1/geography/provinces/' + id + '/districts');
            const json = await res.json();
            const rows = json.data || [];
            rows.forEach((d) => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.name;
                if (String(d.id) === String(selected)) opt.selected = true;
                ilce.appendChild(opt);
            });
        } catch (e) {}
    };
    il.addEventListener('change', () => { load(); });
})();
</script>
@endpush
@endsection
