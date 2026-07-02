@extends('layouts.admin')

@section('title', 'Yeni Satıcı')

@section('content')
<div class="card p-4" style="max-width:540px;">
    <form method="POST" action="{{ route('admin.vendors.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Ad</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">E-posta (giriş için)</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Şifre</label>
            <input type="password" name="password" class="form-control" required>
            @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Şifre (tekrar)</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Telefon</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">İl</label>
                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">İlçe</label>
                <input type="text" name="district" class="form-control" value="{{ old('district') }}">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Adres</label>
            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Logo / Görsel</label>
            <input type="file" name="logo" class="form-control" accept="image/*">
            <div class="form-text">JPG, PNG. En fazla 2 MB.</div>
            @error('logo')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Açıklama</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>
        @if(isset($businessTypes) && $businessTypes->isNotEmpty())
            <div class="mb-3">
                <label class="form-label fw-semibold">İş kolu (pazaryeri etiketi)</label>
                <select name="business_types[]" class="form-select" multiple size="6">
                    @foreach($businessTypes as $bt)
                        <option value="{{ $bt->id }}" @selected(in_array($bt->id, old('business_types', [])))>{{ $bt->name }}</option>
                    @endforeach
                </select>
                <div class="form-text">Ctrl/Cmd ile çoklu seçim. Filtreleme ve satıcı profilinde görünür.</div>
            </div>
        @endif
        <div class="mb-4 form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', true))>
            <label class="form-check-label">Aktif</label>
        </div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">İptal</a>
    </form>
</div>
@endsection
