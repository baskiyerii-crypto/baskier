@extends('layouts.admin')
@section('title', 'Blog içe aktar')
@section('content')
<div class="card p-4" style="max-width:640px;">
    <p class="small text-muted">CSV sütunları: title/baslik, body/icerik, category/kategori, meta_title (opsiyonel)</p>
    <form method="post" action="{{ route('admin.blog.import.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Dosya</label>
            <input type="file" name="file" class="form-control" accept=".csv,.txt,.xlsx" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Durum</label>
            <select name="status" class="form-select">
                <option value="draft">Taslak</option>
                <option value="published">Yayında</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Kategori filtresi (opsiyonel)</label>
            <input type="text" name="category_filter" class="form-control" value="{{ old('category_filter') }}">
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="humanize" value="1" class="form-check-input" id="humanize" checked>
            <label for="humanize" class="form-check-label">AI ile insanlaştır</label>
        </div>
        <button class="btn btn-primary">İçe aktar</button>
    </form>
</div>
@endsection
