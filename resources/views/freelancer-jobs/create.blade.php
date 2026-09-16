@extends('layouts.app')

@section('title', 'Yeni iş ilanı')

@section('content')
<div class="content-shell py-4" style="max-width:640px;">
    <h1 class="h5 mb-4">Yeni iş ilanı</h1>
    <form action="{{ route('freelancer-jobs.store') }}" method="post" class="bg-white rounded-4 shadow-sm p-4">
        @csrf
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="category" class="form-select" required>
                <option value="logo" @selected(old('category', $selectedCategory ?? '') === 'logo')>Logo & kurumsal</option>
                <option value="wordpress" @selected(old('category', $selectedCategory ?? '') === 'wordpress')>Web & WordPress</option>
                <option value="brochure" @selected(old('category', $selectedCategory ?? '') === 'brochure')>Broşür & katalog</option>
                <option value="digital" @selected(old('category', $selectedCategory ?? '') === 'digital')>Dijital içerik</option>
                <option value="other" @selected(old('category', $selectedCategory ?? '') === 'other')>Diğer</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Başlık</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255">
        </div>
        <div class="mb-3">
            <label class="form-label">Açıklama</label>
            <textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea>
        </div>
        <div class="row g-2">
            <div class="col-md-6 mb-3">
                <label class="form-label">Bütçe min (₺)</label>
                <input type="number" step="0.01" name="budget_min" class="form-control" value="{{ old('budget_min') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Bütçe max (₺)</label>
                <input type="number" step="0.01" name="budget_max" class="form-control" value="{{ old('budget_max') }}">
            </div>
        </div>
        <button type="submit" class="btn btn-warning rounded-pill">Yayınla</button>
    </form>
</div>
@endsection
