@extends('layouts.app')
@section('title', 'Talep #' . $quoteRequest->id . ' - BaskıYeri')
@section('content')
<div class="by-container py-8 md:py-12">
    <div class="mb-5">
        <a href="{{ route('quote-requests.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Tüm Taleplerime Dön
        </a>
    </div>

    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    <div class="by-card p-6 md:p-8 bg-surface border border-border mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-muted">Talep #{{ $quoteRequest->id }}</span>
                    <span class="text-xs text-muted">·</span>
                    <span class="text-xs text-muted">{{ $quoteRequest->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink">{{ $quoteRequest->title }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-muted">
                    <span>Kategori: <strong class="text-ink font-medium">{{ $quoteRequest->category?->name ?? 'Belirtilmedi' }}</strong></span>
                    @if($quoteRequest->city)
                        <span>·</span>
                        <span>Teslimat: <strong class="text-ink font-medium">{{ $quoteRequest->city }} / {{ $quoteRequest->district }}</strong></span>
                    @endif
                </div>
            </div>
            <div>
                @if($quoteRequest->status === 'open')
                    <x-badge variant="success">{{ __('panel.status_open') }}</x-badge>
                @else
                    <x-badge variant="neutral">{{ __('panel.status_closed') }}</x-badge>
                @endif
            </div>
        </div>

        @if($quoteRequest->description)
            <div class="mt-5 pt-5 border-t border-border text-sm text-ink leading-relaxed">
                {!! nl2br(e($quoteRequest->description)) !!}
            </div>
        @endif
    </div>

    @if($quoteRequest->items && $quoteRequest->items->isNotEmpty())
        <div class="by-card p-6 md:p-8 bg-surface border border-border mb-8">
            <h2 class="font-heading text-lg font-bold text-ink mb-4">Talep Kalemleri</h2>
            <div class="space-y-4">
                @foreach($quoteRequest->items as $idx => $it)
                    <div class="p-4 rounded-xl border border-border bg-canvas/50">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <span class="text-xs font-bold text-muted uppercase">Kalem #{{ $idx + 1 }}</span>
                                <h3 class="text-base font-semibold text-ink">
                                    {{ $it->product?->name ?? $it->category?->name ?? 'Özel Kalem' }}
                                </h3>
                                <p class="text-xs text-muted mt-0.5">
                                    {{ $it->category?->name }}
                                    @if($it->quantity)
                                        · <strong class="text-ink">{{ $it->quantity }} {{ $it->unit ?: 'adet' }}</strong>
                                    @endif
                                </p>
                            </div>
                            @if($it->files && $it->files->isNotEmpty())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-canvas text-ink border border-border">
                                    {{ $it->files->count() }} ek dosya
                                </span>
                            @endif
                        </div>

                        @if($it->spec)
                            <div class="mt-3 p-3 rounded-lg bg-surface border border-border text-xs text-ink whitespace-pre-line">
                                {{ $it->spec }}
                            </div>
                        @endif

                        @if($it->files && $it->files->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-border">
                                @foreach($it->files as $f)
                                    <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-surface text-xs font-medium text-ink hover:border-cta hover:text-cta transition-colors" href="{{ asset('storage/'.$f->path) }}" target="_blank" rel="noopener">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        {{ \Illuminate\Support\Str::limit($f->original_name ?? basename($f->path), 28) }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mb-4">
        <h2 class="font-heading text-xl font-bold text-ink">Gelen Üretici Teklifleri</h2>
        <p class="text-xs text-muted mt-0.5">Üreticiler tarafından bu talebe sunulan fiyat ve termin teklifleri</p>
    </div>

    @if($quoteRequest->quotes->isEmpty())
        <div class="by-card p-8 text-center bg-surface border border-border">
            <p class="text-sm text-muted mb-0">Henüz bu talebe teklif iletilmedi. Üreticiler incelediğinde burada listelenecektir.</p>
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-2">
            @foreach($quoteRequest->quotes as $q)
                <div class="by-card p-6 bg-surface border border-border flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-bold text-ink">{{ $q->vendor?->name }}</h3>
                                @if($q->vendor)
                                    <a href="{{ route('vendors.show', $q->vendor->slug) }}" class="text-xs text-cta hover:underline font-medium">
                                        Mağaza Profilini İncele →
                                    </a>
                                @endif
                            </div>
                            <div>
                                <span class="text-lg font-extrabold text-ink">₺{{ number_format($q->amount, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        @if($q->delivery_days)
                            <div class="mt-2 text-xs text-muted">
                                Tahmini Termin: <strong class="text-ink font-semibold">{{ $q->delivery_days }} iş günü</strong>
                            </div>
                        @endif

                        @if($q->note)
                            <div class="mt-3 p-3 rounded-lg bg-canvas text-xs text-muted leading-relaxed">
                                {{ $q->note }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 pt-4 border-t border-border">
                        @if($quoteRequest->status === 'open' && $q->status === 'pending')
                            <form method="POST" action="{{ route('quote-requests.select-quote', [$quoteRequest, $q]) }}" class="space-y-3">
                                @csrf
                                <label class="flex items-start gap-2 text-xs text-ink cursor-pointer">
                                    <input type="checkbox" name="share_my_contact" value="1" class="mt-0.5 h-4 w-4 rounded border-border text-cta focus:ring-cta" required>
                                    <span>{{ __('panel.consent_share_my_contact') }}</span>
                                </label>
                                <label class="flex items-start gap-2 text-xs text-ink cursor-pointer">
                                    <input type="checkbox" name="accept_vendor_contact" value="1" class="mt-0.5 h-4 w-4 rounded border-border text-cta focus:ring-cta" required>
                                    <span>{{ __('panel.consent_accept_vendor_contact') }}</span>
                                </label>
                                <div>
                                    @include('partials.legal-scroll-gate', [
                                        'slug' => 'consent-'.$q->id,
                                        'label' => __('panel.consent_legal'),
                                        'field' => 'accept_consent',
                                        'contractHtml' => $consentContract->content_html ?? null,
                                    ])
                                </div>
                                <button type="submit" class="btn btn-cta w-full text-xs py-2.5">
                                    {{ __('panel.select_this_quote') }}
                                </button>
                            </form>
                        @elseif($q->status === 'selected')
                            <div class="flex items-center gap-2">
                                <x-badge variant="success">{{ __('panel.selected_quote') }}</x-badge>
                                <span class="text-xs text-muted">Bu teklif onaylandı.</span>
                            </div>
                        @else
                            <x-badge variant="neutral">{{ $q->status }}</x-badge>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
