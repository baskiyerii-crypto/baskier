@extends('layouts.account')

@section('title', 'Yeni destek talebi')

@section('content')
    <nav class="mb-3"><a href="{{ route('account.support.index') }}" class="small text-muted text-decoration-none">← Taleplerim</a></nav>
    <h1 class="h4 fw-bold mb-1">Yeni destek talebi</h1>
    <p class="small text-muted mb-4">Ekibimiz en kısa sürede yanıtlar.</p>

    <div class="rounded-4 border-0 shadow-sm p-4 p-md-5 bg-white" style="max-width: 640px;">
        <form method="post" action="{{ route('account.support.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-medium">Konu</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="form-control form-control-lg rounded-3 @error('subject') is-invalid @enderror" required maxlength="255" placeholder="Kısa başlık">
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label fw-medium">Mesaj</label>
                <textarea name="body" rows="6" class="form-control rounded-3 @error('body') is-invalid @enderror" required maxlength="5000" placeholder="Sorununuzu veya talebinizi detaylı yazın">{{ old('body') }}</textarea>
                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold w-100 w-md-auto">Gönder</button>
        </form>
    </div>
@endsection
