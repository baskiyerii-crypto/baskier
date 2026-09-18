@extends('layouts.app')

@section('title', $vendor->name . ' - ' . __('ui.vendors'))

@section('content')
@php
    $logoUrl = $vendor->logoUrl();
@endphp
<div class="by-container py-6 sm:py-8">
    <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="relative h-36 sm:h-44 bg-gradient-to-br from-slate-900 via-slate-800 to-orange-900">
            @if($vendor->coverUrl())
                <img src="{{ $vendor->coverUrl() }}" alt="" class="absolute inset-0 h-full w-full object-cover">
            @endif
            <div class="absolute inset-0 opacity-40" style="background-image:radial-gradient(circle at 18% 40%, rgba(255,255,255,.18), transparent 42%), radial-gradient(circle at 82% 18%, rgba(251,146,60,.35), transparent 38%);"></div>
        </div>
        <div class="px-5 pb-6 sm:px-8 sm:pb-8">
            <div class="relative z-10 mt-4 flex flex-col gap-4 sm:mt-5 sm:flex-row sm:items-end">
                <div class="h-24 w-24 sm:h-28 sm:w-28 shrink-0 overflow-hidden rounded-2xl border-[3px] border-white bg-white shadow-md ring-1 ring-slate-200/60">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $vendor->name }}" class="h-full w-full object-contain p-2.5" loading="lazy">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-orange-50 text-3xl font-extrabold text-orange-800">
                            {{ mb_strtoupper(mb_substr($vendor->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0 pb-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="truncate text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $vendor->name }}</h1>
                        <x-trust-badge :vendor="$vendor" size="md" />
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ __('ui.marketplace_vendor') }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        @if($vendor->rating_average)
                            <span class="text-sm font-semibold text-amber-600">★ {{ number_format($vendor->rating_average, 1) }} · {{ $vendor->reviews_count }}</span>
                        @endif
                        @if($vendor->risk_band ?? null)
                            @php
                                $riskLabel = match($vendor->risk_band) {
                                    'safe' => 'Risksiz',
                                    'medium' => 'Orta risk',
                                    'risky' => 'Riskli',
                                    default => null,
                                };
                                $riskClass = match($vendor->risk_band) {
                                    'safe' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                                    'medium' => 'border-amber-200 bg-amber-50 text-amber-800',
                                    'risky' => 'border-rose-200 bg-rose-50 text-rose-800',
                                    default => 'border-slate-200 bg-slate-50 text-slate-600',
                                };
                            @endphp
                            @if($riskLabel)
                                <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $riskClass }}">{{ $riskLabel }}</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            @if($vendor->businessTypes->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach($vendor->businessTypes as $bt)
                        <span class="rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-900">{{ $bt->name }}</span>
                    @endforeach
                </div>
            @endif

            @if($vendor->description)
                <p class="mt-4 max-w-3xl text-sm leading-relaxed text-slate-600">{{ $vendor->description }}</p>
            @endif
        </div>
    </section>

    <section class="mt-8">
        <h2 class="text-lg font-bold tracking-tight text-slate-900">{{ __('ui.store_products') }}</h2>
        @if($products->isEmpty())
            <p class="mt-3 text-sm text-slate-500">{{ __('ui.no_vendor_products') }}</p>
        @else
            <div class="mt-4 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($products as $product)
                    <a href="{{ route('products.show', $product->slug) }}" class="group overflow-hidden rounded-2xl border border-slate-200/80 bg-white transition hover:border-orange-200 hover:shadow-md">
                        <div class="aspect-[4/3] bg-slate-100">
                            @if($product->displayImageUrl())
                                <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->localizedName() }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]" loading="lazy">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-sm text-slate-400">{{ __('ui.no_image') }}</div>
                            @endif
                        </div>
                        <div class="p-3 sm:p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ $product->category?->localizedName() ?? __('ui.category') }}</p>
                            <p class="mt-1 line-clamp-2 text-sm font-semibold text-slate-900">{{ $product->localizedName() }}</p>
                            <p class="mt-2 text-sm font-bold text-orange-900">₺{{ number_format($product->price, 2, ',', '.') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </section>
</div>
@endsection
