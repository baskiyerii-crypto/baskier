@extends('layouts.app')
@section('title', 'Teklif Talebi Oluştur')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 p-4">
                <h1 class="h4 mb-4">Teklif al</h1>
                @if(!auth()->check())
                    <div class="alert alert-info small mb-4">
                        Formu doldurup gönderdiğinizde, talebinizi yayınlayabilmemiz için <strong>üye olmanız</strong> veya <strong>giriş yapmanız</strong> istenecek. Bilgileriniz kaydedilir.
                    </div>
                @endif
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                <form method="POST" action="{{ route('quote-requests.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Baslik</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Aciklama</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Il</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Ilce</label>
                            <input type="text" name="district" class="form-control" value="{{ old('district') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adres</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Iletisim telefonu</label>
                        <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone') }}">
                    </div>
                    <button type="submit" class="btn btn-warning">{{ auth()->check() ? 'Gönder' : 'Devam et (üye ol / giriş yap)' }}</button>
                    @auth
                    <a href="{{ route('quote-requests.index') }}" class="btn btn-outline-secondary">İptal</a>
                    @else
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">İptal</a>
                    @endauth
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
