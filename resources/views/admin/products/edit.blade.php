@extends('layouts.admin')

@section('title', 'Ürün Düzenle')

@section('content')
<div class="card p-4" style="max-width:720px;">
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label fw-semibold">Ürün adı</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Kategori</label>
                <select name="category_id" class="form-select" required>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Satıcı</label>
                <select name="vendor_id" class="form-select" required>
                    @foreach($vendors as $v)<option value="{{ $v->id }}" @selected(old('vendor_id', $product->vendor_id) == $v->id)>{{ $v->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">SKU / Stok kodu</label>
            <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" placeholder="Örn: KRT-001">
            @error('sku')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Fiyat (₺)</label>
                <input type="number" name="price" step="0.01" min="0" class="form-control" value="{{ old('price', $product->price) }}" required>
                @error('price')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Stok adedi</label>
                <input type="number" name="stock" min="0" class="form-control" value="{{ old('stock', $product->stock) }}" required>
                @error('stock')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Ürün tipi</label>
            <select name="product_type" class="form-select">
                <option value="physical" @selected(old('product_type', $product->product_type ?? 'physical') == 'physical')>Fiziksel</option>
                <option value="digital" @selected(old('product_type', $product->product_type ?? 'physical') == 'digital')>Dijital</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Ürün görseli</label>
            @if($product->main_image)
                <div class="mb-2"><img src="{{ asset('storage/'.$product->main_image) }}" alt="" class="rounded" style="max-height:140px; max-width:220px; object-fit:cover;"></div>
            @endif
            <input type="file" name="main_image" class="form-control" accept="image/*">
            <div class="form-text">Değiştirmek için yeni dosya seçin. JPG, PNG, en fazla 2 MB.</div>
            @error('main_image')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Dijital ürün linki</label>
            <input type="url" name="digital_link" class="form-control" value="{{ old('digital_link', $product->digital_link) }}">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Kısa açıklama (liste / önizleme)</label>
            <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Detaylı açıklama</label>
            <textarea name="description" class="form-control" rows="5">{{ old('description', $product->description) }}</textarea>
        </div>
        <div class="mb-4">
            <div class="form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $product->is_active))>
                <label class="form-check-label">Aktif</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Güncelle</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">İptal</a>
    </form>
</div>
@endsection
