@extends('layouts.app')

@section('title', 'Hizmet Talepleri - BaskıYeri')

@section('content')
<div class="by-container py-8 md:py-12">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-muted mb-1">Pazar Yeri & Freelance</p>
            <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink">
                @if(!empty($currentCategory))
                    {{ \App\Support\FreelancerCategories::label($currentCategory) }}
                @else
                    Hizmet & Tasarım Talepleri
                @endif
            </h1>
            <p class="text-sm text-muted mt-1">Grafik tasarım, baskı hazırlık ve montaj alanında açık iş talepleri</p>
        </div>
        <div class="flex flex-wrap gap-2.5 items-center">
            @auth
                @if(auth()->user()->isCustomer())
                    <a href="{{ route('service-requests.create') }}" class="btn btn-cta text-sm">
                        + Hizmet Talebi Oluştur
                    </a>
                    <a href="{{ route('service-requests.my') }}" class="btn btn-secondary text-sm">
                        Taleplerim
                    </a>
                @endif
            @endauth
        </div>
    </div>

    @if($jobs->isEmpty())
        <div class="by-card p-12 text-center bg-surface border border-border">
            <div class="w-12 h-12 mx-auto rounded-full bg-canvas flex items-center justify-center text-muted mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-base font-bold text-ink">Şu an açık hizmet talebi bulunmuyor</h3>
            <p class="text-sm text-muted mt-1 max-w-md mx-auto">İhtiyacınız olan tasarım veya baskı hazırlık işi için hemen yeni bir talep açabilirsiniz.</p>
            @auth
                @if(auth()->user()->isCustomer())
                    <div class="mt-4">
                        <a href="{{ route('service-requests.create') }}" class="btn btn-cta text-xs">Talep Oluştur</a>
                    </div>
                @endif
            @endauth
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($jobs as $job)
                <div class="by-card bg-surface border border-border flex flex-col justify-between overflow-hidden hover:border-cta/50 transition-colors">
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-canvas text-ink border border-border">
                                {{ \App\Support\FreelancerCategories::label($job->category) }}
                            </span>
                            @if($job->bids_count !== null)
                                <span class="text-xs text-muted font-medium">{{ $job->bids_count }} teklif</span>
                            @endif
                        </div>

                        <h2 class="font-heading text-lg font-bold text-ink mb-2">
                            <a href="{{ route('service-requests.show', $job) }}" class="hover:text-cta transition-colors">
                                {{ Str::limit($job->title, 60) }}
                            </a>
                        </h2>

                        <p class="text-xs text-muted line-clamp-3 leading-relaxed mb-4">
                            {{ Str::limit($job->description, 120) }}
                        </p>

                        @if($job->budget_min || $job->budget_max)
                            <div class="text-xs font-medium text-ink bg-canvas p-2.5 rounded-lg border border-border mb-3">
                                <span class="text-muted font-normal">{{ __('home.budget') }}:</span>
                                <strong class="font-semibold text-ink">
                                    ₺{{ $job->budget_min ? number_format($job->budget_min, 0, ',', '.') : '0' }}
                                    – ₺{{ $job->budget_max ? number_format($job->budget_max, 0, ',', '.') : 'Limit Belirtilmedi' }}
                                </strong>
                            </div>
                        @endif

                        @if($job->user)
                            <div class="text-[11px] text-muted flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>{{ $job->user->name }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-4 bg-canvas/40 border-t border-border">
                        <a href="{{ route('service-requests.show', $job) }}" class="btn btn-secondary w-full text-xs py-2">
                            Talebi İncele ve Teklif Ver →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $jobs->links() }}
        </div>
    @endif
</div>
@endsection
