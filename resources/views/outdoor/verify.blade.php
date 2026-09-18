@extends('layouts.app')
@section('title', 'Pano doğrulama')
@section('content')
<div class="by-container py-10">
    <div class="mx-auto max-w-lg by-card p-6">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Pano kimliği</p>
        <h1 class="mt-1 text-2xl font-extrabold">{{ $inventory->title }}</h1>
        <p class="mt-2 text-sm text-slate-600">{{ $inventory->locationLabel() }}</p>
        <p class="mt-4 text-sm">Son geçerli asım: {{ $lastProof?->captured_at?->format('d.m.Y H:i') ?: 'Kayıt yok' }}</p>
    </div>
</div>
@endsection
