@extends('layouts.app')

@php
    $currentCategory = null;
    if (request('category')) {
        $currentCategory = $categories->firstWhere('slug', request('category')) 
            ?? $categories->flatMap->children->firstWhere('slug', request('category'));
    } elseif (request('category_id')) {
        $currentCategory = $categories->firstWhere('id', (int) request('category_id')) 
            ?? $categories->flatMap->children->firstWhere('id', (int) request('category_id'));
    }

    $pageTitle = $currentCategory 
        ? $currentCategory->localizedName() . ' Ürünleri' 
        : (request('type') === 'digital' ? 'Dijital Baskı & Şablonlar' : 'Tüm Ürünler');

    $crumbs = [
        ['title' => 'Ürünler', 'url' => route('products.index')],
    ];
    if ($currentCategory) {
        $crumbs[] = ['title' => $currentCategory->localizedName(), 'url' => ''];
    }

    $hasActiveFilters = request('q') || request('category') || request('category_id') || request('type') || (request('sort') && request('sort') !== 'fair');
@endphp

@section('title', $pageTitle)
@section('meta_description', $currentCategory ? ($currentCategory->localizedName() . ' kategorisindeki en kaliteli baskı ve üretim ürünlerini inceleyin, doğrudan sipariş verin.') : 'BaskıYeri ürün kataloğu; kartvizit, broşür, etiket, ambalaj, tabela ve promosyon ürünlerinde yüzlerce seçeneği üreticilerden sunar.')

@section('content')
    <div class="by-container py-6">
        {{-- Breadcrumbs --}}
        <x-breadcrumb :items="$crumbs" />

        {{-- Mobile Filter Trigger & Results Summary Bar --}}
        <div class="lg:hidden flex items-center justify-between gap-3 mb-4 rounded-xl border border-[#DEDAD2] bg-white p-3 shadow-xs">
            <button type="button" class="inline-flex min-h-[40px] items-center gap-2 rounded-lg bg-[#F7F5F0] px-3.5 py-2 text-xs font-bold text-[#182023] border border-[#DEDAD2]" data-drawer-open="product-filter-drawer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="4" x2="20" y1="21" y2="21"/><line x1="4" x2="20" y1="3" y2="3"/><line x1="4" x2="20" y1="12" y2="12"/><circle cx="14" cy="3" r="2"/><circle cx="8" cy="12" r="2"/><circle cx="16" cy="21" r="2"/></svg>
                <span>Filtrele & Sırala</span>
            </button>
            <span class="text-xs text-[#596166] font-medium">
                {{ $products->total() }} ürün
            </span>
        </div>

        {{-- Mobile Filter Drawer --}}
        <x-drawer id="product-filter-drawer" title="Filtrele ve Sırala" side="left">
            <form action="{{ route('products.index') }}" method="GET" class="space-y-5">
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#596166] mb-2">Sıralama</label>
                    <select name="sort" class="w-full min-h-[44px] rounded-lg border border-[#DEDAD2] bg-white px-3 text-sm text-[#182023] outline-none">
                        <option value="fair" @selected(request('sort', 'fair') === 'fair')>Adil Sıralama</option>
                        <option value="newest" @selected(request('sort') === 'newest')>En Yeni</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Fiyat: Düşükten Yükseğe</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Fiyat: Yüksekten Düşüğe</option>
                        <option value="rating" @selected(request('sort') === 'rating')>En Yüksek Puan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#596166] mb-2">Kategoriler</label>
                    <div class="space-y-1 max-h-72 overflow-y-auto pr-1">
                        <a href="{{ route('products.index', array_filter(request()->only('q', 'type', 'sort'))) }}" class="block rounded-lg px-3 py-2 text-sm font-semibold {{ !request('category') && !request('category_id') ? 'bg-[#F7F5F0] text-[#C2410C]' : 'text-[#182023] hover:bg-[#F7F5F0]' }}">
                            Tümü
                        </a>
                        @foreach($categories as $cat)
                            @php $isCatActive = request('category') === $cat->slug || (int) request('category_id') === $cat->id; @endphp
                            <a href="{{ route('products.index', array_merge(array_filter(request()->only('q', 'type', 'sort')), ['category' => $cat->slug])) }}" class="block rounded-lg px-3 py-2 text-sm {{ $isCatActive ? 'bg-[#F7F5F0] text-[#C2410C] font-bold' : 'text-[#182023] hover:bg-[#F7F5F0]' }}">
                                {{ $cat->localizedName() }}
                            </a>
                            @foreach($cat->children as $sub)
                                @php $isSubActive = request('category') === $sub->slug || (int) request('category_id') === $sub->id; @endphp
                                <a href="{{ route('products.index', array_merge(array_filter(request()->only('q', 'type', 'sort')), ['category' => $sub->slug])) }}" class="block rounded-lg pl-6 pr-3 py-1.5 text-xs {{ $isSubActive ? 'bg-[#F7F5F0] text-[#C2410C] font-bold' : 'text-[#596166] hover:bg-[#F7F5F0]' }}">
                                    ↳ {{ $sub->localizedName() }}
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 border-t border-[#DEDAD2] flex gap-2">
                    <x-button type="submit" variant="primary" fullWidth>Uygula</x-button>
                    @if($hasActiveFilters)
                        <a href="{{ route('products.index') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-lg border border-[#DEDAD2] px-3 text-xs font-semibold text-[#596166] hover:bg-[#F7F5F0] text-center shrink-0">Temizle</a>
                    @endif
                </div>
            </form>
        </x-drawer>

        {{-- Desktop 2-Column Grid: Left Sidebar (Desktop) + Products (Main) --}}
        <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
            {{-- Desktop Left Filter Sidebar --}}
            <aside class="hidden lg:block">
                <div class="rounded-xl border border-[#DEDAD2] bg-white p-5 shadow-xs sticky top-20">
                    <div class="flex items-center justify-between border-b border-[#DEDAD2] pb-3 mb-4">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#596166]">Kategoriler</h2>
                        @if(request('category') || request('category_id'))
                            <a href="{{ route('products.index', array_filter(request()->only('q', 'type', 'sort'))) }}" class="text-[11px] font-semibold text-[#C2410C] hover:underline">
                                Temizle
                            </a>
                        @endif
                    </div>

                    @if($categories->isEmpty())
                        <p class="text-xs text-[#596166]">{{ __('ui.no_categories') }}</p>
                    @else
                        <div class="space-y-1">
                            <a href="{{ route('products.index', array_filter(request()->only('q', 'type', 'sort'))) }}"
                               class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-semibold transition {{ !request('category') && !request('category_id') ? 'bg-[#F7F5F0] text-[#C2410C]' : 'text-[#182023] hover:bg-[#F7F5F0]' }}">
                                <span>{{ __('ui.all') }}</span>
                                <span class="text-xs text-[#596166]">({{ $products->total() }})</span>
                            </a>
                            @foreach($categories as $category)
                                @php 
                                    $isCurrent = request('category') === $category->slug || (int) request('category_id') === $category->id;
                                @endphp
                                <a href="{{ route('products.index', array_merge(array_filter(request()->only('q', 'type', 'sort')), ['category' => $category->slug])) }}"
                                   class="flex items-center justify-between rounded-lg px-3 py-2 text-sm transition {{ $isCurrent ? 'bg-[#F7F5F0] text-[#C2410C] font-bold border-l-2 border-[#C2410C]' : 'text-[#182023] hover:bg-[#F7F5F0]' }}">
                                    <span class="truncate">{{ $category->localizedName() }}</span>
                                    <span class="text-xs text-[#596166]">›</span>
                                </a>
                                @foreach($category->children as $child)
                                    @php 
                                        $isSubCurrent = request('category') === $child->slug || (int) request('category_id') === $child->id;
                                    @endphp
                                    <a href="{{ route('products.index', array_merge(array_filter(request()->only('q', 'type', 'sort')), ['category' => $child->slug])) }}"
                                       class="ml-3 flex items-center justify-between rounded-lg px-3 py-1.5 text-xs transition {{ $isSubCurrent ? 'bg-[#F7F5F0] text-[#C2410C] font-bold' : 'text-[#596166] hover:bg-[#F7F5F0]' }}">
                                        <span class="truncate">{{ $child->localizedName() }}</span>
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                    @endif

                    {{-- Quick links to RFQ --}}
                    <div class="mt-6 pt-5 border-t border-[#DEDAD2]">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#596166] mb-2">Özel İşiniz mi Var?</p>
                        <p class="text-xs text-[#596166] leading-relaxed mb-3">İstediğiniz ölçü ve adet için üreticilerden doğrudan fiyat teklifi alın.</p>
                        <x-button href="{{ route('quote-requests.create', ['type' => 'physical_quote']) }}" variant="secondary" size="sm" fullWidth>
                            Teklif Talebi Aç
                        </x-button>
                    </div>
                </div>
            </aside>

            {{-- Main Products Content --}}
            <section class="min-w-0">
                {{-- Header & Sorting Bar --}}
                <div class="rounded-xl border border-[#DEDAD2] bg-white p-4 sm:p-5 shadow-xs mb-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#182023]">
                                {{ $pageTitle }}
                            </h1>
                            <p class="text-xs text-[#596166] mt-0.5">
                                Toplam <span class="font-bold text-[#182023]">{{ $products->total() }}</span> ürün listeleniyor
                            </p>
                        </div>

                        {{-- Search & Sorting Form --}}
                        <form class="flex flex-wrap items-center gap-2" action="{{ route('products.index') }}" method="GET">
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            @if(request('category_id'))
                                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                            @endif
                            @if(request('type'))
                                <input type="hidden" name="type" value="{{ request('type') }}">
                            @endif

                            <div class="flex-1 sm:flex-none">
                                <select name="sort" onchange="this.form.submit()" class="w-full sm:w-auto min-h-[40px] rounded-lg border border-[#DEDAD2] bg-[#F7F5F0]/60 px-3 py-1.5 text-xs font-semibold text-[#182023] outline-none focus:border-[#C2410C]">
                                    <option value="fair" @selected(request('sort', 'fair') === 'fair')>Adil Sıralama</option>
                                    <option value="newest" @selected(request('sort') === 'newest')>En Yeni</option>
                                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Fiyat: Düşükten Yükseğe</option>
                                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Fiyat: Yüksekten Düşüğe</option>
                                    <option value="rating" @selected(request('sort') === 'rating')>En Yüksek Puan</option>
                                </select>
                            </div>

                            <div class="relative flex-1 sm:w-48">
                                <input
                                    name="q"
                                    type="search"
                                    value="{{ request('q') }}"
                                    placeholder="{{ __('ui.search_placeholder') }}"
                                    class="w-full min-h-[40px] rounded-lg border border-[#DEDAD2] bg-white px-3 py-1.5 text-xs text-[#182023] outline-none focus:border-[#C2410C]"
                                />
                            </div>
                            <x-button type="submit" variant="primary" size="sm">Ara</x-button>
                        </form>
                    </div>

                    {{-- Active Filter Tags --}}
                    @if($hasActiveFilters)
                        <div class="mt-4 pt-3 border-t border-[#DEDAD2] flex flex-wrap items-center gap-2 text-xs">
                            <span class="text-[#596166] font-semibold">Aktif Filtreler:</span>
                            @if(request('q'))
                                <span class="inline-flex items-center gap-1 rounded-md bg-[#F7F5F0] border border-[#DEDAD2] px-2 py-0.5 text-[#182023]">
                                    Arama: "{{ request('q') }}"
                                </span>
                            @endif
                            @if($currentCategory)
                                <span class="inline-flex items-center gap-1 rounded-md bg-[#F7F5F0] border border-[#DEDAD2] px-2 py-0.5 text-[#182023]">
                                    Kategori: {{ $currentCategory->localizedName() }}
                                </span>
                            @endif
                            @if(request('type'))
                                <span class="inline-flex items-center gap-1 rounded-md bg-[#F7F5F0] border border-[#DEDAD2] px-2 py-0.5 text-[#182023]">
                                    Tür: {{ request('type') }}
                                </span>
                            @endif
                            <a href="{{ route('products.index') }}" class="text-[#C2410C] font-semibold hover:underline ml-1">
                                Tüm Filtreleri Temizle
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Products Grid --}}
                @if($products->isEmpty())
                    <x-empty-state 
                        title="Ürün bulunamadı" 
                        message="Aramanıza veya seçtiğiniz kategoriye uygun ürün bulunamadı."
                        actionText="Filtreleri Temizle"
                        actionUrl="{{ route('products.index') }}"
                    />
                @else
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-6">
                        <x-pagination :paginator="$products" />
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
