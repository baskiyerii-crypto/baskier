@extends('layouts.app')

@section('title', $vendor->name . ' - Satıcı - BaskıYeri Pazaryeri')

@section('content')
<div class="by-container py-6">
    <div class="overflow-hidden by-card">
        <div class="aspect-[16/5] bg-gradient-to-br from-slate-800 via-slate-700 to-orange-900 relative">
            <div class="absolute inset-0 opacity-30" style="background-image:radial-gradient(circle at 20% 50%, rgba(255,255,255,.15), transparent 40%), radial-gradient(circle at 80% 20%, rgba(251,146,60,.3), transparent 35%);"></div>
        </div>
        <div class="px-6 pb-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:-mt-12 relative z-10">
                <div class="h-28 w-28 shrink-0 overflow-hidden rounded-2xl border-4 border-white bg-white shadow-lg">
                    @if($vendor->logo)
                        <img src="{{ asset('storage/'.$vendor->logo) }}" alt="{{ $vendor->name }}" class="h-full w-full object-contain p-2">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-orange-50 text-3xl font-extrabold text-orange-800">
                            {{ mb_strtoupper(mb_substr($vendor->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 pb-1">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $vendor->name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Pazaryeri satıcısı</p>
                    @if($vendor->rating_average)
                        <p class="mt-2 text-sm text-amber-600">★ {{ number_format($vendor->rating_average, 1) }} · {{ $vendor->reviews_count }} değerlendirme</p>
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
                            <span class="by-badge mt-2 inline-flex {{ $riskClass }}">{{ $riskLabel }}</span>
                        @endif
                    @endif
                </div>
            </div>

            @if($vendor->businessTypes->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($vendor->businessTypes as $bt)
                        <span class="by-badge border-orange-200 bg-orange-50 text-orange-900">{{ $bt->name }}</span>
                    @endforeach
                </div>
            @endif

            @if($vendor->description)
                <p class="mt-4 max-w-3xl text-sm leading-relaxed text-slate-600">{{ $vendor->description }}</p>
            @endif
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-lg font-bold tracking-tight text-slate-900">Mağaza ürünleri</h2>
        @if($products->isEmpty())
            <p class="mt-3 text-sm text-slate-500">Bu satıcıya ait ürün bulunamadı.</p>
        @else
            <div class="mt-4 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($products as $product)
                    <a href="{{ route('products.show', $product->slug) }}" class="group by-card by-card-hover overflow-hidden">
                        <div class="aspect-[4/3] bg-slate-100">
                            @if($product->main_image)
                                <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-slate-400 text-sm">Görsel yok</div>
                            @endif
                        </div>
                        <div class="p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $product->category?->name ?? 'Kategori' }}</p>
                            <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $product->name }}</p>
                            <p class="mt-2 text-sm font-bold text-orange-900">₺{{ number_format($product->price, 2, ',', '.') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </div>
</div>
@endsection
