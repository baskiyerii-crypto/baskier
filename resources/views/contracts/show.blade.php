@extends('layouts.app')

@section('title', ($contract->title ?? 'Sözleşme') . ' - BaskıYeri')

@section('content')
<div class="content-shell py-6">
    <div class="by-container">
        <div class="by-card p-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <div class="text-xs text-slate-500">Sürüm: v{{ (int) $contract->version }}</div>
                    <h1 class="text-2xl font-bold tracking-tight mt-1">{{ $contract->title }}</h1>
                </div>
                <a href="{{ url()->previous() }}" class="by-btn-secondary">Geri dön</a>
            </div>

            <div class="by-divider my-5"></div>

            @if(empty($contract->content_html))
                <div class="by-surface-amber p-4">
                    <div class="by-accent-bar mb-3"></div>
                    <div class="font-semibold">Bu sözleşme içeriği henüz eklenmedi.</div>
                    <div class="text-sm text-slate-600 mt-1">Admin panelden sözleşme metnini doldurabilirsiniz.</div>
                </div>
            @else
                <article class="prose prose-slate max-w-none prose-a:text-indigo-600 prose-a:no-underline hover:prose-a:underline">
                    {!! $contract->content_html !!}
                </article>
            @endif
        </div>
    </div>
</div>
@endsection

