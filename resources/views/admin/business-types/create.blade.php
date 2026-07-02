@extends('layouts.admin')

@section('title', 'Yeni iş kolu')

@section('content')
<div class="card p-4" style="max-width:480px;">
    <form method="POST" action="{{ route('admin.business-types.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Görünen ad</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255">
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Slug (boş bırakılırsa otomatik)</label>
            <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="ornek: matbaa">
            @error('slug')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Sıra</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
        </div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="{{ route('admin.business-types.index') }}" class="btn btn-outline-secondary">İptal</a>
    </form>
</div>
@endsection
