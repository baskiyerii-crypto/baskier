@extends('layouts.app')
@section('title', 'Blog')
@section('content')
<div class="by-container py-8">
    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Blog</h1>
    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="by-card by-card-hover p-5">
                <p class="text-xs uppercase tracking-wider text-slate-500">{{ $post->category ?? 'Genel' }}</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ $post->title }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags($post->body), 120) }}</p>
            </a>
        @empty
            <p class="text-sm text-slate-500">Henüz yazı yok.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
</div>
@endsection
