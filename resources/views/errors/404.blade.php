@extends('layouts.app')

@section('title', '404 - Sayfa Bulunamadı - BaskıYeri')

@section('content')
<div class="by-container py-16 md:py-24 text-center">
    <div class="max-w-md mx-auto">
        <div class="w-16 h-16 mx-auto rounded-full bg-canvas flex items-center justify-center text-muted mb-4 border border-border">
            <span class="font-mono text-2xl font-extrabold text-ink">404</span>
        </div>
        <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink mb-2">Sayfa Bulunamadı</h1>
        <p class="text-xs text-muted mb-6 leading-relaxed">
            Aradığınız sayfa kaldırılmış, adı değiştirilmiş veya geçici olarak kullanım dışı kalmış olabilir.
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-cta text-xs">Ana Sayfaya Dön</a>
            <a href="{{ route('products.index') }}" class="btn btn-secondary text-xs">Ürünleri Keşfet</a>
        </div>
    </div>
</div>
@endsection
