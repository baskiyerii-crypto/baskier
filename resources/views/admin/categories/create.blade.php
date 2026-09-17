@extends('layouts.admin')

@section('title', 'Yeni Kategori')

@section('content')
<div class="card p-4" style="max-width:540px;">
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="channel" value="{{ old('channel', $channel ?? 'physical_quote') }}">
        <div class="mb-3">
            <label class="form-label fw-semibold">Ad</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Ad (EN)</label>
            <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Kanal</label>
            <select name="channel" class="form-select" required>
                <option value="physical_quote" @selected(old('channel', $channel ?? '') === 'physical_quote')>Fiziksel + Teklif</option>
                <option value="freelancer" @selected(old('channel', $channel ?? '') === 'freelancer')>Freelancer</option>
                <option value="tabela" @selected(old('channel', $channel ?? '') === 'tabela')>Tabela</option>
                <option value="ozalit" @selected(old('channel', $channel ?? '') === 'ozalit')>Ozalit / kağıt çıktı</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Üst kategori</label>
            <select name="parent_id" class="form-select">
                <option value="">— Yok —</option>
                @foreach($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_id') == $p->id)>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Açıklama</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Termin süresi (iş günü)</label>
            <input type="number" name="termin_days" class="form-control" value="{{ old('termin_days') }}" min="0" placeholder="Örn: 5">
            <div class="form-text">Sipariş onayından itibaren termin tarihi bu güne göre hesaplanır.</div>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="requires_quote" value="1" class="form-check-input" @checked(old('requires_quote'))>
            <label class="form-check-label">Teklif ile satılır</label>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Görsel</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        <div class="mb-4 form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', true))>
            <label class="form-check-label">Aktif</label>
        </div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="{{ route('admin.categories.index', ['channel' => $channel ?? 'physical_quote']) }}" class="btn btn-outline-secondary">İptal</a>
    </form>
</div>
@endsection
