@extends('layouts.app')
@section('title', 'Baskı Dünyası & Blog - BaskıYeri')
@section('content')
<div class="by-container py-10 md:py-16">
    <div class="max-w-2xl mb-10">
        <p class="text-xs font-bold uppercase tracking-wider text-muted mb-1">Rehber ve Makaleler</p>
        <h1 class="font-heading text-3xl md:text-4xl font-bold tracking-tight text-ink">Baskı Dünyasından Notlar</h1>
        <p class="text-sm text-muted mt-2">Baskı teknikleri, kağıt gramajları, kurumsal kimlik tasarımı ve üretim süreçlerine dair ipuçları</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="by-card p-6 bg-surface border border-border flex flex-col justify-between hover:border-cta/50 transition-colors">
                <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-canvas text-muted border border-border">
                        {{ $post->category ?? 'Baskı Rehberi' }}
                    </span>
                    <h2 class="font-heading text-lg font-bold text-ink mt-3 mb-2 hover:text-cta transition-colors">
                        {{ $post->title }}
                    </h2>
                    <p class="text-xs text-muted line-clamp-3 leading-relaxed">
                        {{ \Illuminate\Support\Str::limit(strip_tags($post->body), 140) }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-xs text-muted">
                    <span>Devamını Oku →</span>
                    <span>{{ $post->created_at?->format('d.m.Y') }}</span>
                </div>
            </a>
        @empty
            <div class="sm:col-span-3 text-center py-12 text-sm text-muted">
                Henüz yayınlanmış bir blog yazısı bulunmuyor.
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $posts->links() }}</div>
</div>
@endsection
