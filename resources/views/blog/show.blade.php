@extends('layouts.app')
@section('title', $post->title)
@section('content')
<article class="by-container py-8 max-w-3xl">
    <p class="text-xs uppercase tracking-wider text-slate-500">{{ $post->category }}</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $post->title }}</h1>
    <div class="prose mt-6 max-w-none text-slate-700">{!! nl2br(e($post->body)) !!}</div>
</article>
@endsection
