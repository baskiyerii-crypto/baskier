@extends('layouts.app')

@section('title', __('ui.contact').' - BaskıYeri')

@section('content')
<div class="by-container py-12 md:py-16">
    <div class="mx-auto max-w-2xl">
        <div class="mb-8 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-muted">{{ __('ui.contact') }}</p>
            <h1 class="font-heading text-3xl md:text-4xl font-bold tracking-tight text-ink mt-1">{{ __('ui.contact_title') }}</h1>
            <p class="mt-2 text-xs text-muted">{{ __('ui.contact_body') }}</p>
        </div>

        <div class="by-card p-6 md:p-8 bg-surface border border-border">
            <div class="space-y-4 text-xs text-ink">
                @if($address)
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-canvas/40 border border-border">
                        <span class="text-muted text-sm mt-0.5">📍</span>
                        <div>
                            <span class="font-bold text-ink block mb-0.5">{{ __('panel.address') }}</span>
                            <span class="text-muted leading-relaxed">{{ $address }}</span>
                        </div>
                    </div>
                @endif
                @if($phone)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-canvas/40 border border-border">
                        <span class="text-muted text-sm">📞</span>
                        <div>
                            <span class="font-bold text-ink block mb-0.5">{{ __('panel.phone') }}</span>
                            <a class="text-cta hover:underline font-semibold" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a>
                        </div>
                    </div>
                @endif
                @if($email)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-canvas/40 border border-border">
                        <span class="text-muted text-sm">✉️</span>
                        <div>
                            <span class="font-bold text-ink block mb-0.5">{{ __('panel.email') }}</span>
                            <a class="text-cta hover:underline font-semibold" href="mailto:{{ $email }}">{{ $email }}</a>
                        </div>
                    </div>
                @endif
                @if($instagram)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-canvas/40 border border-border">
                        <span class="text-muted text-sm">📷</span>
                        <div>
                            <span class="font-bold text-ink block mb-0.5">Instagram</span>
                            <a class="text-cta hover:underline font-semibold" href="{{ str_starts_with($instagram, 'http') ? $instagram : 'https://instagram.com/'.ltrim($instagram, '@') }}" target="_blank" rel="noopener">{{ $instagram }}</a>
                        </div>
                    </div>
                @endif
                @if($website)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-canvas/40 border border-border">
                        <span class="text-muted text-sm">🌐</span>
                        <div>
                            <span class="font-bold text-ink block mb-0.5">Web Sitesi</span>
                            <a class="text-cta hover:underline font-semibold" href="{{ $website }}" target="_blank" rel="noopener">{{ $website }}</a>
                        </div>
                    </div>
                @endif
                @if(! $address && ! $phone && ! $email)
                    <p class="text-muted text-center py-4">{{ __('ui.contact_empty') }}</p>
                @endif
            </div>

            @if($map)
                <div class="mt-6 overflow-hidden rounded-xl border border-border">
                    <iframe src="{{ $map }}" class="h-64 w-full border-0" loading="lazy" title="{{ __('ui.contact') }}"></iframe>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
