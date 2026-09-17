@extends('layouts.app')
@section('title', $post->title . ' - BaskıYeri Blog')
@section('content')
<div class="by-container py-10 md:py-16">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('blog.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Tüm Yazılara Dön
            </a>
            <span class="block text-xs font-bold uppercase tracking-wider text-muted mb-2">{{ $post->category ?? 'Baskı Rehberi' }}</span>
            <h1 class="font-heading text-3xl md:text-4xl font-bold tracking-tight text-ink">{{ $post->title }}</h1>
            <div class="text-xs text-muted mt-2">{{ $post->created_at?->format('d F Y') }}</div>
        </div>

        <div class="by-card p-6 md:p-10 bg-surface border border-border">
            <article class="prose max-w-none text-sm text-ink leading-relaxed space-y-4">
                {!! nl2br(e($post->body)) !!}
            </article>
        </div>
    </div>
</div>
@endsection
