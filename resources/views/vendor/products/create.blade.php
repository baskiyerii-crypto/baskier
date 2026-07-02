@extends('layouts.vendor')

@section('title', 'Yeni Ürün')

@section('content')
<div class="card p-4" style="max-width:640px;">
        @if(!$freelancerModuleActive)
            <div class="alert alert-warning small">
                Dijital/freelancer ürün eklemek için <a href="{{ route('vendor.subscriptions.index') }}">modül aboneliğini</a> aktifleştirmelisiniz.
            </div>
        @endif
        <form method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Ürün adı</label>
                <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Kategori</label>
                <select name="category_id" class="form-select rounded-3" required>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Fiyat (₺)</label>
                    <input type="number" name="price" step="0.01" min="0" class="form-control rounded-3" value="{{ old('price') }}" required>
                    @error('price')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Stok</label>
                    <input type="number" name="stock" min="0" class="form-control rounded-3" value="{{ old('stock', 0) }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Ürün tipi</label>
                <select name="product_type" class="form-select">
                    <option value="physical" @selected(old('product_type') == 'physical')>Fiziksel</option>
                    <option value="digital" @selected(old('product_type') == 'digital') @disabled(!$freelancerModuleActive)>Dijital (Freelancer)</option>
                </select>
            </div>
            <div class="mb-3 product-digital-field">
                <label class="form-label fw-semibold">Ürün görseli</label>
                <input type="file" name="main_image" class="form-control" accept="image/*">
                <div class="form-text">JPG, PNG, en fazla 2 MB.</div>
                @error('main_image')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 product-digital-link-field" style="display:none;">
                <label class="form-label fw-semibold">Dijital ürün linki (indirme/erişim)</label>
                <input type="url" name="digital_link" class="form-control" value="{{ old('digital_link') }}" placeholder="https://...">
                @error('digital_link')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Kısa açıklama</label>
                <textarea name="short_description" class="form-control" rows="2">{{ old('short_description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Detaylı açıklama</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Varyantlar (isteğe bağlı)</label>
                <textarea name="variant_lines" class="form-control font-monospace" rows="4" placeholder="Kırmızı XL|25|40|SKU-RED-XL&#10;Mavi L|0|20|SKU-BLUE-L">{{ old('variant_lines') }}</textarea>
                <div class="form-text">Her satır: Varyant Adı | Fiyat Farkı | Stok | SKU</div>
            </div>
            <button type="submit" class="btn btn-success">Ürünü Ekle</button>
            <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">İptal</a>
        </form>
</div>
<script>document.querySelector('[name=product_type]').addEventListener('change', function(){ var isDigital = this.value==='digital'; document.querySelector('.product-digital-field').style.display = isDigital ? 'none' : 'block'; document.querySelector('.product-digital-link-field').style.display = isDigital ? 'block' : 'none'; }); if(document.querySelector('[name=product_type]').value==='digital'){ document.querySelector('.product-digital-field').style.display='none'; document.querySelector('.product-digital-link-field').style.display='block'; }</script>
@endsection
