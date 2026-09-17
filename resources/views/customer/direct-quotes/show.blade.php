@extends('layouts.account')
@section('title', $directQuote->title . ' - BaskıYeri')
@section('content')
<div class="mb-5">
    <a href="{{ route('customer.direct-quotes.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Doğrudan Tekliflere Dön
    </a>
</div>

<div class="by-card p-6 md:p-8 bg-surface border border-border max-w-2xl space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold tracking-tight text-ink">{{ $directQuote->title }}</h1>
            <p class="text-xs text-muted mt-1">{{ $directQuote->vendor?->name }} · {{ $directQuote->created_at->format('d.m.Y H:i') }}</p>
        </div>
        <x-badge variant="neutral">{{ \App\Support\UiLabels::status($directQuote->status) }}</x-badge>
    </div>

    <div class="text-xs text-ink whitespace-pre-line leading-relaxed p-4 rounded-xl bg-canvas/50 border border-border">
        {{ $directQuote->body }}
    </div>

    @if($directQuote->offer_amount)
        <div class="p-4 rounded-xl bg-canvas border border-border">
            <div class="flex items-center justify-between">
                <span class="text-xs text-muted">{{ __('panel.offer') }}:</span>
                <span class="text-lg font-bold text-cta">₺{{ number_format($directQuote->offer_amount, 2, ',', '.') }}</span>
            </div>
            @if($directQuote->vendor_note)
                <div class="text-xs text-muted mt-2 pt-2 border-t border-border">
                    <strong class="text-ink">Satıcı Notu:</strong> {{ $directQuote->vendor_note }}
                </div>
            @endif
        </div>
    @endif

    @if($directQuote->status === 'offered')
        <form method="post" action="{{ route('customer.direct-quotes.accept', $directQuote) }}" class="space-y-3 border-t border-border pt-4">
            @csrf
            <label class="flex items-center gap-2 text-xs text-ink cursor-pointer">
                <input type="checkbox" name="share_my_contact" value="1" class="h-4 w-4 rounded border-border text-cta focus:ring-cta" required>
                <span>{{ __('panel.consent_share_my_contact') }}</span>
            </label>
            <label class="flex items-center gap-2 text-xs text-ink cursor-pointer">
                <input type="checkbox" name="accept_vendor_contact" value="1" class="h-4 w-4 rounded border-border text-cta focus:ring-cta" required>
                <span>{{ __('panel.consent_accept_vendor_contact') }}</span>
            </label>
            <p class="text-[11px] text-muted">{{ __('panel.consent_legal') }}</p>
            <button class="btn btn-cta text-xs py-2 px-6">{{ __('panel.accept_and_share') }}</button>
        </form>
    @endif
</div>
@endsection
