@extends('layouts.app')

@section('title', ($contract->title ?? 'Sözleşme') . ' - BaskıYeri')

@section('content')
<div class="by-container py-10 md:py-16">
    <div class="max-w-3xl mx-auto">
        <div class="by-card p-6 md:p-10 bg-surface border border-border">
            <div class="flex items-start justify-between gap-4 flex-wrap pb-4 border-b border-border mb-6">
                <div>
                    <span class="text-xs font-semibold text-muted">Sürüm: v{{ (int) $contract->version }}</span>
                    <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink mt-1">{{ $contract->title }}</h1>
                </div>
                <a href="{{ url()->previous() }}" class="btn btn-secondary text-xs">
                    ← Geri Dön
                </a>
            </div>

            @if(empty($contract->content_html))
                <div class="p-6 rounded-xl bg-canvas text-center text-xs text-muted">
                    Bu sözleşme metni henüz eklenmemiştir.
                </div>
            @else
                <article class="prose max-w-none text-xs text-ink leading-relaxed space-y-3">
                    {!! $contract->content_html !!}
                </article>
            @endif
        </div>
    </div>
</div>
@endsection
