@extends('layouts.vendor')

@section('title', 'Ürün Düzenle')

@section('content')
<div class="card p-4" style="max-width:640px;">
        <form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label small fw-semibold">Ürün adı</label>
                <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $product->name) }}" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Kategori</label>
                <select name="category_id" class="form-select rounded-3" required>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Fiyat (₺)</label>
                    <input type="number" name="price" step="0.01" min="0" class="form-control rounded-3" value="{{ old('price', $product->price) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Stok</label>
                    <input type="number" name="stock" min="0" class="form-control rounded-3" value="{{ old('stock', $product->stock) }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Ürün tipi</label>
                <select name="product_type" class="form-select">
                    <option value="physical" @selected(old('product_type', $product->product_type ?? 'physical') == 'physical')>Fiziksel</option>
                    <option value="digital" @selected(old('product_type', $product->product_type ?? 'physical') == 'digital')>Dijital</option>
                </select>
            </div>
            <div class="mb-3 product-digital-field">
                <label class="form-label fw-semibold">Ürün görseli</label>
                @if($product->main_image)<div class="mb-2"><img src="{{ asset('storage/'.$product->main_image) }}" alt="" class="rounded" style="max-height:80px;"></div>@endif
                <input type="file" name="main_image" class="form-control" accept="image/*">
            </div>
            <div class="mb-3 product-digital-link-field" style="display:{{ ($product->product_type ?? 'physical') === 'digital' ? 'block' : 'none' }};">
                <label class="form-label fw-semibold">Dijital ürün linki</label>
                <input type="url" name="digital_link" class="form-control" value="{{ old('digital_link', $product->digital_link) }}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Kısa açıklama</label>
                <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
            </div>
            <div class="mb-4 form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $product->is_active))>
                <label class="form-check-label">Aktif (sitede görünsün)</label>
            </div>
            <button type="submit" class="btn btn-success">Güncelle</button>
            <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">İptal</a>
        </form>
</div>
<script>document.querySelector('[name=product_type]').addEventListener('change', function(){ var isDigital = this.value==='digital'; document.querySelector('.product-digital-field').style.display = isDigital ? 'none' : 'block'; document.querySelector('.product-digital-link-field').style.display = isDigital ? 'block' : 'none'; });</script>
@endsection
