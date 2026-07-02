@extends('layouts.admin')

@section('title', 'Kategori Düzenle')

@section('content')
<div class="card p-4" style="max-width:540px;">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label fw-semibold">Ad</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Üst kategori</label>
            <select name="parent_id" class="form-select">
                <option value="">— Yok —</option>
                @foreach($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id) == $p->id)>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Açıklama</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Teslimat süresi (iş günü)</label>
                <input type="number" name="delivery_days" class="form-control" value="{{ old('delivery_days', $category->delivery_days) }}" min="0">
            </div>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="requires_quote" value="1" class="form-check-input" @checked(old('requires_quote', $category->requires_quote))>
            <label class="form-check-label">Teklif ile satılır</label>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Görsel</label>
            @if($category->image)
                <div class="mb-2"><img src="{{ asset('storage/'.$category->image) }}" alt="" class="rounded" style="max-height:120px; max-width:200px; object-fit:cover;"></div>
            @endif
            <input type="file" name="image" class="form-control" accept="image/*">
            <div class="form-text">Değiştirmek için yeni dosya seçin. JPG, PNG, en fazla 2 MB.</div>
            @error('image')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4 form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $category->is_active))>
            <label class="form-check-label">Aktif</label>
        </div>
        <button type="submit" class="btn btn-primary">Güncelle</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">İptal</a>
    </form>
</div>
@endsection
