@extends('layouts.app')

@section('title', __('ui.contact').' - BaskıYeri')

@section('content')
<div class="by-container py-10">
    <div class="mx-auto max-w-2xl by-card p-6 md:p-8">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('ui.contact') }}</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ __('ui.contact_title') }}</h1>
        <p class="mt-2 text-sm text-slate-600">{{ __('ui.contact_body') }}</p>

        <div class="mt-6 space-y-3 text-sm text-slate-700">
            @if($address)
                <p><span class="font-semibold">{{ __('panel.address') }}:</span> {{ $address }}</p>
            @endif
            @if($phone)
                <p><span class="font-semibold">{{ __('panel.phone') }}:</span> <a class="by-link" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p>
            @endif
            @if($email)
                <p><span class="font-semibold">{{ __('panel.email') }}:</span> <a class="by-link" href="mailto:{{ $email }}">{{ $email }}</a></p>
            @endif
            @if($instagram)
                <p><span class="font-semibold">Instagram:</span>
                    <a class="by-link" href="{{ str_starts_with($instagram, 'http') ? $instagram : 'https://instagram.com/'.ltrim($instagram, '@') }}" target="_blank" rel="noopener">{{ $instagram }}</a>
                </p>
            @endif
            @if($website)
                <p><span class="font-semibold">Website:</span> <a class="by-link" href="{{ $website }}" target="_blank" rel="noopener">{{ $website }}</a></p>
            @endif
            @if(! $address && ! $phone && ! $email)
                <p class="text-slate-500">{{ __('ui.contact_empty') }}</p>
            @endif
        </div>

        @if($map)
            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                <iframe src="{{ $map }}" class="h-56 w-full border-0" loading="lazy" title="{{ __('ui.contact') }}"></iframe>
            </div>
        @endif
    </div>
</div>
@endsection
