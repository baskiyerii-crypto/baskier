@extends('layouts.app')

@section('title', __('home.title'))
@section('meta_description', 'BaskıYeri; kurumsal matbaa, etiket, kutu, tabela, promosyon ve tasarım ihtiyaçlarınız için doğrudan üreticilerden güvenilir çözümler sunar.')

@section('content')
    {{-- Hero Section (Single Responsive WebP/AVIF Image, No Auto-Slider) --}}
    <section class="relative bg-white border-b border-[#DEDAD2]">
        <div class="by-container py-8 sm:py-12 lg:py-16">
            <div class="grid items-center gap-8 lg:grid-cols-12 lg:gap-12">
                {{-- Hero Copy & Search (7 cols) --}}
                <div class="lg:col-span-7">
                    <div class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-orange-50 px-3.5 py-1 text-xs font-bold text-[#C2410C] mb-4">
                        <span class="h-2 w-2 rounded-full bg-[#C2410C]"></span>
                        <span>{{ __('home.badge') }}</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-5xl font-extrabold tracking-tight text-[#182023] leading-[1.15]">
                        {{ __('home.hero_line_1') }}
                        <span class="text-[#C2410C]">{{ __('home.hero_line_2') }}</span>{{ __('home.hero_line_3') }}
                    </h1>

                    <p class="mt-4 max-w-xl text-base sm:text-lg leading-relaxed text-[#596166]">
                        {{ __('home.hero_body') }}
                    </p>

                    {{-- Search Form with Primary Product Discovery CTA and Secondary Quote CTA --}}
                    <div class="mt-6 sm:mt-8 rounded-xl border border-[#DEDAD2] bg-[#F7F5F0] p-3 sm:p-4 shadow-xs">
                        <form action="{{ route('products.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-1">
                                <input 
                                    type="search"
                                    name="q" 
                                    value="{{ request('q') }}" 
                                    placeholder="{{ __('home.search_placeholder') }}" 
                                    class="w-full min-h-[46px] rounded-lg border border-[#DEDAD2] bg-white px-4 pl-10 text-sm text-[#182023] placeholder-[#596166] focus:border-[#C2410C] focus:ring-2 focus:ring-[#C2410C]/20 outline-none"
                                />
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[#596166]">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                </span>
                            </div>
                            <x-button type="submit" variant="primary" size="md" class="shrink-0">
                                {{ __('home.search') }}
                            </x-button>
                            <x-button href="{{ route('quote-requests.create', ['type' => 'physical_quote']) }}" variant="secondary" size="md" class="shrink-0">
                                {{ __('home.quote') }}
                            </x-button>
                        </form>

                        <div class="mt-3 flex flex-wrap items-center gap-2 pt-2 border-t border-[#DEDAD2]/70 text-xs text-[#596166]">
                            <span class="font-semibold text-[#182023]">Hızlı Keşif:</span>
                            <a href="{{ route('products.index') }}" class="rounded-md bg-white border border-[#DEDAD2] px-2.5 py-1 text-[#182023] hover:border-[#C2410C] hover:text-[#C2410C] transition">Tüm Ürünler</a>
                            <a href="{{ route('quote-requests.create', ['type' => 'physical_quote']) }}" class="rounded-md bg-white border border-[#DEDAD2] px-2.5 py-1 text-[#182023] hover:border-[#C2410C] hover:text-[#C2410C] transition">Özel Baskı Teklifi</a>
                            <a href="{{ route('service-requests.index') }}" class="rounded-md bg-white border border-[#DEDAD2] px-2.5 py-1 text-[#182023] hover:border-[#C2410C] hover:text-[#C2410C] transition">Hizmet Talepleri</a>
                            <a href="{{ route('vendors.index') }}" class="rounded-md bg-white border border-[#DEDAD2] px-2.5 py-1 text-[#182023] hover:border-[#C2410C] hover:text-[#C2410C] transition">Üreticiler</a>
                        </div>
                    </div>
                </div>

                {{-- Hero Visual (5 cols) --}}
                <div class="lg:col-span-5">
                    <div class="relative overflow-hidden rounded-2xl border border-[#DEDAD2] bg-[#F7F5F0] shadow-sm aspect-4/3 sm:aspect-16/10 lg:aspect-4/3">
                        <picture>
                            <source type="image/avif" srcset="{{ asset('images/home/hero-desktop.avif') }} 1280w, {{ asset('images/home/hero-mobile.avif') }} 720w" sizes="(max-width: 1024px) 100vw, 42vw">
                            <source type="image/webp" srcset="{{ asset('images/home/hero-desktop.webp') }} 1280w, {{ asset('images/home/hero-mobile.webp') }} 720w" sizes="(max-width: 1024px) 100vw, 42vw">
                            <img 
                                src="{{ asset('images/home/hero-desktop.webp') }}" 
                                alt="BaskıYeri Modern Baskı ve Üretim Stüdyosu" 
                                width="1280" 
                                height="720"
                                fetchpriority="high"
                                loading="eager"
                                class="h-full w-full object-cover"
                            />
                        </picture>
                        <div class="absolute inset-0 bg-gradient-to-t from-[#182023]/60 via-transparent to-transparent pointer-events-none"></div>
                        <div class="absolute bottom-4 left-4 right-4 text-white">
                            <p class="text-xs font-bold uppercase tracking-wider text-orange-200">Modern Baskı Ekosistemi</p>
                            <p class="text-sm font-semibold text-white/95">Doğrudan matbaacı ve üreticilerle güvenli üretim akışı</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4 Core Production Pathways --}}
    <section class="by-container py-8 sm:py-10">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('products.index') }}" class="group rounded-xl border border-[#DEDAD2] bg-white p-5 shadow-xs transition duration-150 ease-out hover:-translate-y-0.5 hover:shadow-md hover:border-[#C2410C]">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-[#C2410C] mb-3 group-hover:bg-[#C2410C] group-hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </div>
                <h2 class="text-base font-bold text-[#182023] tracking-tight">{{ __('home.path_ready_title') }}</h2>
                <p class="mt-1 text-xs text-[#596166] leading-relaxed">{{ __('home.path_ready_body') }}</p>
                <span class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-[#C2410C]">{{ __('home.path_ready_cta') }} →</span>
            </a>

            <a href="{{ route('quote-requests.create', ['type' => 'physical_quote']) }}" class="group rounded-xl border border-[#DEDAD2] bg-white p-5 shadow-xs transition duration-150 ease-out hover:-translate-y-0.5 hover:shadow-md hover:border-[#C2410C]">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-[#C2410C] mb-3 group-hover:bg-[#C2410C] group-hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <h2 class="text-base font-bold text-[#182023] tracking-tight">{{ __('home.path_quote_title') }}</h2>
                <p class="mt-1 text-xs text-[#596166] leading-relaxed">{{ __('home.path_quote_body') }}</p>
                <span class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-[#C2410C]">{{ __('home.path_quote_cta') }} →</span>
            </a>

            <a href="{{ route('quote-requests.create', ['type' => 'tabela']) }}" class="group rounded-xl border border-[#DEDAD2] bg-white p-5 shadow-xs transition duration-150 ease-out hover:-translate-y-0.5 hover:shadow-md hover:border-[#C2410C]">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-[#C2410C] mb-3 group-hover:bg-[#C2410C] group-hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                </div>
                <h2 class="text-base font-bold text-[#182023] tracking-tight">{{ __('home.path_tabela_title') }}</h2>
                <p class="mt-1 text-xs text-[#596166] leading-relaxed">{{ __('home.path_tabela_body') }}</p>
                <span class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-[#C2410C]">{{ __('home.path_tabela_cta') }} →</span>
            </a>

            <a href="{{ route('service-requests.index') }}" class="group rounded-xl border border-[#DEDAD2] bg-white p-5 shadow-xs transition duration-150 ease-out hover:-translate-y-0.5 hover:shadow-md hover:border-[#C2410C]">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-[#C2410C] mb-3 group-hover:bg-[#C2410C] group-hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </div>
                <h2 class="text-base font-bold text-[#182023] tracking-tight">{{ __('home.path_freelancer_title') }}</h2>
                <p class="mt-1 text-xs text-[#596166] leading-relaxed">{{ __('home.path_freelancer_body') }}</p>
                <span class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-[#C2410C]">{{ __('home.path_freelancer_cta') }} →</span>
            </a>
        </div>
    </section>

    {{-- Popular Product Categories --}}
    <section class="by-container py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-[#596166]">{{ __('home.collection_eyebrow') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-[#182023]">{{ __('home.collection_title') }}</h2>
                <p class="mt-1 text-sm text-[#596166]">{{ __('home.collection_body') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex min-h-[40px] items-center rounded-lg border border-[#DEDAD2] bg-white px-4 text-xs font-bold text-[#182023] hover:bg-[#F7F5F0] transition">
                {{ __('home.all_products') }} →
            </a>
        </div>

        @if($categories->isEmpty())
            <x-empty-state 
                title="Kategori bulunamadı" 
                message="Şu anda görüntülenecek aktif kategori bulunmuyor."
            />
        @else
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                @foreach($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="group rounded-xl border border-[#DEDAD2] bg-white overflow-hidden shadow-xs hover:-translate-y-0.5 hover:shadow-md transition">
                        <div class="aspect-4/3 bg-[#F7F5F0] overflow-hidden">
                            @if($category->image)
                                <img 
                                    src="{{ asset('storage/' . $category->image) }}" 
                                    alt="{{ $category->localizedName() }}" 
                                    width="400"
                                    height="300"
                                    loading="lazy" 
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                />
                            @else
                                <img 
                                    src="{{ asset('images/placeholder-category.svg') }}" 
                                    alt="{{ $category->localizedName() }}" 
                                    width="400"
                                    height="300"
                                    loading="lazy" 
                                    class="h-full w-full object-contain p-4"
                                />
                            @endif
                        </div>
                        <div class="p-3.5 sm:p-4">
                            <h3 class="text-sm font-bold text-[#182023] group-hover:text-[#C2410C] transition">{{ $category->localizedName() }}</h3>
                            @if($category->products_count ?? false)
                                <p class="text-[11px] text-[#596166] mt-0.5">{{ $category->products_count }} ürün</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Fair Product Discovery Section ("Sizin İçin Ürünler" before digital) --}}
    <section class="by-container py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-[#596166]">{{ __('home.featured_eyebrow') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-[#182023]">{{ __('home.featured_title') }}</h2>
                <p class="mt-1 text-sm text-[#596166]">{{ __('home.featured_body') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex min-h-[40px] items-center rounded-lg border border-[#DEDAD2] bg-white px-4 text-xs font-bold text-[#182023] hover:bg-[#F7F5F0] transition">
                {{ __('home.see_all_products') }} →
            </a>
        </div>

        @php
            $displayDiscovery = isset($discoveryProducts) && $discoveryProducts->isNotEmpty() ? $discoveryProducts : $featuredProducts;
        @endphp

        @if($displayDiscovery->isEmpty())
            <x-empty-state 
                title="Ürün bulunamadı" 
                message="{{ __('home.no_products') }}"
                actionText="Katalogu Gör"
                actionUrl="{{ route('products.index') }}"
            />
        @else
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                @foreach($displayDiscovery as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Service Requests & Freelancer Designers --}}
    <section class="by-container py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-[#596166]">{{ __('home.service_eyebrow') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-[#182023]">{{ __('home.service_title') }}</h2>
                <p class="mt-1 text-sm text-[#596166]">{{ __('home.service_body') }}</p>
            </div>
            <a href="{{ route('service-requests.index') }}" class="inline-flex min-h-[40px] items-center rounded-lg border border-[#DEDAD2] bg-white px-4 text-xs font-bold text-[#182023] hover:bg-[#F7F5F0] transition">
                {{ __('home.all_jobs') }} →
            </a>
        </div>

        @if(!empty($freelancerCategories))
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5 mb-8">
                @foreach($freelancerCategories as $cat)
                    <div class="rounded-xl border border-[#DEDAD2] bg-white p-4 shadow-xs hover:-translate-y-0.5 hover:shadow-md transition flex flex-col justify-between">
                        <a href="{{ route('service-requests.index', ['category' => $cat['key']]) }}" class="block">
                            <p class="text-sm font-bold text-[#182023]">{{ $cat['label'] }}</p>
                            <p class="mt-1 text-xs text-[#596166]">{{ __('home.open_jobs', ['count' => $cat['count']]) }}</p>
                        </a>
                        <div class="mt-3 pt-2 border-t border-[#DEDAD2]/60">
                            <a href="{{ route('service-requests.create', ['category' => $cat['key']]) }}" class="text-xs font-bold text-[#C2410C] hover:underline">
                                Talep Aç →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if(isset($freelancerJobs) && $freelancerJobs->isNotEmpty())
            <div class="rounded-xl border border-[#DEDAD2] bg-white p-5 sm:p-6 shadow-xs">
                <p class="text-xs font-bold uppercase tracking-wider text-[#596166] mb-3">{{ __('home.latest_jobs') }}</p>
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach($freelancerJobs->take(3) as $job)
                        <a href="{{ route('service-requests.show', $job) }}" class="rounded-lg border border-[#DEDAD2] bg-[#F7F5F0]/50 p-3.5 hover:bg-white hover:border-[#C2410C] transition">
                            <p class="text-sm font-bold text-[#182023] line-clamp-1">{{ $job->title }}</p>
                            <p class="mt-1 text-xs text-[#596166]">{{ \App\Support\FreelancerCategories::label($job->category) }} · ₺{{ $job->budget_min ? number_format($job->budget_min, 0, ',', '.') : '?' }}+</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- Digital Products & Templates --}}
    @if(isset($digitalProducts) && $digitalProducts->isNotEmpty())
        <section class="by-container py-8 sm:py-10">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-[#596166]">{{ __('home.digital_eyebrow') }}</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight text-[#182023]">{{ __('home.digital_title') }}</h2>
                    <p class="mt-1 text-sm text-[#596166]">{{ __('home.digital_body') }}</p>
                </div>
                <a href="{{ route('products.index', ['type' => 'digital']) }}" class="inline-flex min-h-[40px] items-center rounded-lg border border-[#DEDAD2] bg-white px-4 text-xs font-bold text-[#182023] hover:bg-[#F7F5F0] transition">
                    {{ __('home.see_all') }} →
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                @foreach($digitalProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Authentic Trust & Print Assurance (Honest, strictly no fake reviews) --}}
    <section class="by-container py-8 sm:py-12 border-t border-[#DEDAD2] mt-8">
        <div class="rounded-xl border border-[#DEDAD2] bg-white p-6 sm:p-10 shadow-xs">
            <div class="text-center max-w-2xl mx-auto mb-8">
                <p class="text-xs font-bold uppercase tracking-wider text-[#C2410C]">BaskıYeri Standartları</p>
                <h2 class="mt-1 text-2xl font-bold text-[#182023] tracking-tight">Güvenli Baskı ve Üretim Altyapısı</h2>
                <p class="mt-2 text-sm text-[#596166]">BaskıYeri, doğrudan üretici ve müşteriyi şeffaf kurallarla buluşturur.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-3">
                <div class="flex flex-col items-center text-center p-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#F7F5F0] text-[#C2410C] mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-[#182023]">Doğrulanmış Üreticiler</h3>
                    <p class="mt-1 text-xs text-[#596166] leading-relaxed">Platformdaki üreticiler vergi levhası, yetki evrakları ve performans geçmişine göre otomatik kademelendirilir.</p>
                </div>

                <div class="flex flex-col items-center text-center p-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#F7F5F0] text-[#C2410C] mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-[#182023]">Güvenli Ödeme ve Hakediş</h3>
                    <p class="mt-1 text-xs text-[#596166] leading-relaxed">Ödemeniz siparişiniz onaylanıp üretime alınana kadar güvenle korunur. Onaysız hiçbir işlem tahsil edilmez.</p>
                </div>

                <div class="flex flex-col items-center text-center p-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#F7F5F0] text-[#C2410C] mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-[#182023]">Tasarım Onay Süreci</h3>
                    <p class="mt-1 text-xs text-[#596166] leading-relaxed">Özel üretim işlerde baskı öncesi tasarım onayınızı dijital ortamda verir, revizyonlarınızı kolayca yönetirsiniz.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
