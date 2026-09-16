@extends('layouts.app')

@section('title', __('home.title'))

@section('content')
    <section class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw]">
        <div class="by-container py-10">
            <div class="by-hero" data-hero-slider>
                <div class="absolute inset-0">
                    <div class="by-hero-slide is-active" data-slide>
                        <img class="h-full w-full object-cover" src="https://picsum.photos/1800/1000?random=hero1" alt="BaskıYeri">
                    </div>
                    <div class="by-hero-slide" data-slide>
                        <img class="h-full w-full object-cover" src="https://picsum.photos/1800/1000?random=hero2" alt="BaskıYeri">
                    </div>
                    <div class="by-hero-slide" data-slide>
                        <img class="h-full w-full object-cover" src="https://picsum.photos/1800/1000?random=hero3" alt="BaskıYeri">
                    </div>
                    <div class="absolute inset-0 by-hero-scrim"></div>
                </div>

                <div class="relative grid items-center gap-10 p-7 md:p-10 lg:grid-cols-12 lg:min-h-[560px]">
                    <div class="lg:col-span-7">
                        <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white/90 backdrop-blur">
                            {{ __('home.badge') }}
                        </p>
                        <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-white md:text-6xl">
                            {{ __('home.hero_line_1') }}
                            <span class="bg-gradient-to-r from-cyan-200 to-white bg-clip-text text-transparent">{{ __('home.hero_line_2') }}</span>{{ __('home.hero_line_3') }}
                        </h1>
                        <p class="mt-4 max-w-xl text-base leading-relaxed text-white/80">
                            {{ __('home.hero_body') }}
                        </p>

                        <div class="mt-7 by-card border-white/15 bg-white/10 p-5 backdrop-blur">
                            <form action="{{ route('products.index') }}">
                                <div class="flex flex-col gap-3 md:flex-row">
                                    <input class="by-input md:flex-1 bg-white/90" name="q" value="{{ request('q') }}" placeholder="{{ __('home.search_placeholder') }}" />
                                    <button class="by-btn-primary">{{ __('home.search') }}</button>
                                    <a class="by-btn-cta" href="{{ route('quote-requests.create') }}">{{ __('home.quote') }}</a>
                                </div>
                            </form>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a class="by-badge border-white/15 bg-white/10 text-white hover:bg-white/15" href="{{ route('products.index') }}">{{ __('home.products') }}</a>
                                <a class="by-badge border-white/15 bg-white/10 text-white hover:bg-white/15" href="{{ route('vendors.index') }}">{{ __('home.vendors') }}</a>
                                <a class="by-badge border-white/15 bg-white/10 text-white hover:bg-white/15" href="{{ route('quote-requests.create', ['type' => 'freelancer']) }}">Hizmet al</a>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <a href="{{ route('products.index') }}" class="by-card border-white/15 bg-white/10 p-5 text-white backdrop-blur hover:bg-white/15">
                                <p class="text-xs font-bold uppercase tracking-wider text-white/70">{{ __('home.ready_eyebrow') }}</p>
                                <p class="mt-2 text-lg font-bold">{{ __('home.ready_title') }}</p>
                                <p class="mt-2 text-sm text-white/75">{{ __('home.ready_body') }}</p>
                            </a>
                            <a href="{{ route('quote-requests.create') }}" class="by-card border-white/15 bg-white/10 p-5 text-white backdrop-blur hover:bg-white/15">
                                <p class="text-xs font-bold uppercase tracking-wider text-white/70">{{ __('home.custom_eyebrow') }}</p>
                                <p class="mt-2 text-lg font-bold">{{ __('home.custom_title') }}</p>
                                <p class="mt-2 text-sm text-white/75">{{ __('home.custom_body') }}</p>
                            </a>
                            <a href="{{ route('vendors.index') }}" class="by-card border-white/15 bg-white/10 p-5 text-white backdrop-blur hover:bg-white/15 sm:col-span-2">
                                <p class="text-xs font-bold uppercase tracking-wider text-white/70">{{ __('home.vendor_eyebrow') }}</p>
                                <p class="mt-2 text-lg font-bold">{{ __('home.vendor_title') }}</p>
                                <p class="mt-2 text-sm text-white/75">{{ __('home.vendor_body') }}</p>
                            </a>
                        </div>
                        <div class="mt-5 flex items-center gap-2">
                            <button type="button" class="h-2.5 w-10 rounded-full bg-white/90" data-dot></button>
                            <button type="button" class="h-2.5 w-2.5 rounded-full bg-white/40" data-dot></button>
                            <button type="button" class="h-2.5 w-2.5 rounded-full bg-white/40" data-dot></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="by-container -mt-2">
        <div class="grid gap-4 lg:grid-cols-3">
            <a href="{{ route('products.index') }}" class="by-card by-card-hover p-6">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Hazır ürün</p>
                <p class="mt-2 text-lg font-bold tracking-tight text-slate-900">Katalogtan satın al</p>
                <p class="mt-2 text-sm text-slate-600">Ürünleri gez, sepete ekle, ödeme ile siparişi tamamla.</p>
                <p class="mt-4 text-sm font-semibold text-indigo-600">Ürünlere git →</p>
            </a>
            <a href="{{ route('quote-requests.create') }}" class="by-card by-card-hover p-6">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Teklif</p>
                <p class="mt-2 text-lg font-bold tracking-tight text-slate-900">Özel üretim için teklif al</p>
                <p class="mt-2 text-sm text-slate-600">İhtiyacınızı anlatın, uygun üreticilerin tekliflerini karşılaştırın.</p>
                <p class="mt-4 text-sm font-semibold text-orange-600">Teklif al →</p>
            </a>
            <a href="{{ route('quote-requests.create', ['type' => 'freelancer']) }}" class="by-card by-card-hover p-6">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Freelancer</p>
                <p class="mt-2 text-lg font-bold tracking-tight text-slate-900">Hizmet al</p>
                <p class="mt-2 text-sm text-slate-600">İhtiyacınızı yazın, ilgili freelancerlar fiyat teklifi versin.</p>
                <p class="mt-4 text-sm font-semibold text-indigo-600">Hizmet talebi oluştur →</p>
            </a>
        </div>
    </section>

    <section class="by-container py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Koleksiyon</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Kategoriler</h2>
                <p class="mt-2 text-sm text-slate-600">Baskı ve reklam ürünlerinde aradığınız kategoriye göz atın.</p>
            </div>
            <a href="{{ route('products.index') }}" class="by-btn-secondary">Tüm ürünler</a>
        </div>
            @if($categories->isEmpty())
                <div class="mt-6 by-card p-8 text-center">
                    <p class="text-sm text-slate-600">Kategori bulunamadı.</p>
                </div>
            @else
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="group by-card by-card-hover overflow-hidden">
                            <div class="aspect-[4/3] bg-slate-100">
                                <img src="https://picsum.photos/900/700?random=kategori{{ $category->id }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                            </div>
                            <div class="p-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $category->name }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
    </section>

    <section class="by-container py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Hizmet</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">İş yapanlar & Freelancer</h2>
                <p class="mt-2 text-sm text-slate-600">Tasarım, baskı, web, tabela – ihtiyacınız olan işi seçin, teklif alın.</p>
            </div>
            <a href="{{ route('freelancer-jobs.index') }}" class="by-btn-secondary">Tüm ilanlar</a>
        </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach($freelancerCategories ?? [] as $cat)
                    <div class="by-card by-card-hover overflow-hidden">
                        <a href="{{ route('freelancer-jobs.index', ['category' => $cat['key']]) }}" class="block">
                            <div class="aspect-[4/3] bg-slate-100">
                                <img src="https://picsum.photos/900/700?random={{ $cat['seed'] }}" alt="{{ $cat['label'] }}" class="h-full w-full object-cover">
                            </div>
                            <div class="p-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $cat['label'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $cat['count'] }} açık ilan</p>
                            </div>
                        </a>
                        <div class="p-4 pt-0">
                            <a href="{{ route('quote-requests.create', ['category' => $cat['key']]) }}" class="w-full by-btn-primary">Teklif al</a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(isset($freelancerJobs) && $freelancerJobs->isNotEmpty())
                <div class="mt-10 by-card p-6">
                    <p class="text-sm font-bold text-slate-900">Son eklenen ilanlar</p>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        @foreach($freelancerJobs->take(3) as $job)
                            <a href="{{ route('freelancer-jobs.show', $job) }}" class="rounded-2xl border border-slate-200 bg-white/70 p-4 hover:bg-white">
                                <p class="text-sm font-semibold text-slate-900">{{ Str::limit($job->title, 54) }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $job->category }} · ₺{{ $job->budget_min ? number_format($job->budget_min, 0, ',', '.') : '?' }}+</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
    </section>

    <section class="by-container py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Yeni</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Dijital ürünler</h2>
                <p class="mt-2 text-sm text-slate-600">Şablonlar, grafik paketleri, dijital dosyalar – anında indir, hemen kullan.</p>
            </div>
            <a href="{{ route('products.index', ['type' => 'digital']) }}" class="by-btn-secondary">Tümünü gör</a>
        </div>
            @if(isset($digitalProducts) && $digitalProducts->isNotEmpty())
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($digitalProducts as $product)
                        <a href="{{ route('products.show', $product->slug) }}" class="group by-card by-card-hover overflow-hidden">
                            <div class="aspect-[4/3] bg-slate-100">
                                @if($product->main_image)
                                    <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                                @else
                                    <img src="https://picsum.photos/900/700?random=digital{{ $product->id }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                                @endif
                            </div>
                            <div class="p-4">
                                <span class="by-badge border-indigo-200 bg-indigo-50 text-indigo-800">Dijital</span>
                                <p class="mt-2 truncate text-sm font-semibold text-slate-900">{{ Str::limit($product->name, 40) }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $product->vendor?->name }}</p>
                                <p class="mt-3 text-sm font-extrabold text-slate-900">₺{{ number_format($product->price, 2, ',', '.') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="mt-6 by-card p-8 text-center">
                    <p class="text-sm text-slate-600">Dijital ürünler yakında eklenecek.</p>
                    <a href="{{ route('products.index', ['type' => 'digital']) }}" class="mt-4 inline-flex by-btn-secondary">Dijital ürünlere göz at</a>
                </div>
            @endif
    </section>

    <section class="by-container py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Yeni koleksiyon</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Öne çıkan ürünler</h2>
                <p class="mt-2 text-sm text-slate-600">En güncel baskı ve reklam ürünleri.</p>
            </div>
            <a href="{{ route('products.index') }}" class="by-btn-secondary">Tüm ürünleri gör</a>
        </div>

        @if($featuredProducts->isEmpty())
            <div class="mt-6 by-card p-8 text-center">
                <p class="text-sm text-slate-600">Henüz ürün yok.</p>
            </div>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($featuredProducts as $product)
                    <a href="{{ route('products.show', $product->slug) }}" class="group by-card by-card-hover overflow-hidden">
                        <div class="aspect-[4/3] bg-slate-100">
                            @if($product->main_image)
                                <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                            @else
                                <img src="https://picsum.photos/900/700?random=urun{{ $product->id ?? $loop->index }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                            @endif
                        </div>
                        <div class="p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $product->vendor?->name ?? 'Satıcı' }}</p>
                            <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $product->name }}</p>
                            <div class="mt-3 flex items-center justify-between">
                                <p class="text-sm font-extrabold text-slate-900">₺{{ number_format($product->price, 2, ',', '.') }}</p>
                                <span class="text-xs font-semibold text-indigo-600">Detay →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

@endsection

