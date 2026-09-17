@extends('layouts.app')

@section('title', '500 - Sunucu Hatası - BaskıYeri')

@section('content')
<div class="by-container py-16 md:py-24 text-center">
    <div class="max-w-md mx-auto">
        <div class="w-16 h-16 mx-auto rounded-full bg-canvas flex items-center justify-center text-muted mb-4 border border-border">
            <span class="font-mono text-2xl font-extrabold text-red-600">500</span>
        </div>
        <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink mb-2">Beklenmeyen Bir Hata Oluştu</h1>
        <p class="text-xs text-muted mb-6 leading-relaxed">
            Sistemlerimizde geçici bir aksaklık meydana geldi. Mühendislerimiz durumdan haberdar edildi. Lütfen kısa bir süre sonra tekrar deneyiniz.
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-cta text-xs">Ana Sayfaya Dön</a>
            <a href="{{ route('pages.contact') }}" class="btn btn-secondary text-xs">Bize Ulaşın</a>
        </div>
    </div>
</div>
@endsection
