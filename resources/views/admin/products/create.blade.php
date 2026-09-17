@extends('layouts.admin')
@section('title', 'Yeni Urun')
@section('content')
<div class="card p-4" style="max-width:720px;">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Urun adi</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Urun adi (EN)</label>
            <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Kategori</label>
                <select name="category_id" class="form-select" required>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Satici</label>
                <select name="vendor_id" class="form-select" required>
                    @foreach($vendors as $v)<option value="{{ $v->id }}" @selected(old('vendor_id') == $v->id)>{{ $v->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">SKU</label>
            <input type="text" name="sku" class="form-control" value="{{ old('sku') }}">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Fiyat (TL)</label>
                <input type="number" name="price" step="0.01" min="0" class="form-control" value="{{ old('price') }}" required>
                @error('price')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Stok</label>
                <input type="number" name="stock" min="0" class="form-control" value="{{ old('stock', 0) }}" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Urun tipi</label>
            <select name="product_type" class="form-select">
                <option value="physical" @selected(old('product_type') == 'physical')>Fiziksel</option>
                <option value="digital" @selected(old('product_type') == 'digital')>Dijital</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Urun gorseli</label>
            <input type="file" name="main_image" class="form-control" accept="image/*">
            @error('main_image')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Dijital urun linki</label>
            <input type="url" name="digital_link" class="form-control" value="{{ old('digital_link') }}">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Kisa aciklama</label>
            <textarea name="short_description" class="form-control" rows="2">{{ old('short_description') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Detayli aciklama</label>
            <textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea>
        </div>
        <div class="mb-4">
            <div class="form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', true))>
                <label class="form-check-label">Aktif</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Urun ekle</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Iptal</a>
    </form>
</div>
@endsection
