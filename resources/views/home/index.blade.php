@extends('layouts.app')

@section('title', __('home.title'))

@section('content')
    <section class="w-screen relative left-1/2 right-1/2 ml-[-50vw] mr-[-50vw]">
        <div class="by-container py-10">
            <div class="by-hero" data-hero-slider>
                <div class="absolute inset-0">
                    <div class="by-hero-slide is-active" data-slide>
                        <img class="h-full w-full object-cover" src="{{ asset('images/home/hero-slide-1.png') }}" alt="Matbaa">
                    </div>
                    <div class="by-hero-slide" data-slide>
                        <img class="h-full w-full object-cover" src="{{ asset('images/home/hero-slide-2.png') }}" alt="Baskı">
                    </div>
                    <div class="by-hero-slide" data-slide>
                        <img class="h-full w-full object-cover" src="{{ asset('images/home/hero-slide-3.png') }}" alt="Tabela">
                    </div>
                    <div class="by-hero-slide" data-slide>
                        <img class="h-full w-full object-cover" src="{{ asset('images/home/hero-slide-4.png') }}" alt="Tasarım">
                    </div>
                    <div class="absolute inset-0 by-hero-scrim"></div>
                </div>

                <div class="relative grid items-center gap-10 p-7 md:p-10 lg:grid-cols-12 lg:min-h-140">
                    <div class="lg:col-span-7">
                        <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white/90 backdrop-blur">
                            {{ __('home.badge') }}
                        </p>
                        <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-white md:text-6xl">
                            {{ __('home.hero_line_1') }}
                            <span class="bg-linear-to-r from-cyan-200 to-white bg-clip-text text-transparent">{{ __('home.hero_line_2') }}</span>{{ __('home.hero_line_3') }}
                        </h1>
                        <p class="mt-4 max-w-xl text-base leading-relaxed text-white/80">
                            {{ __('home.hero_body') }}
                        </p>

                        <div class="mt-7 by-card border-white/15 bg-white/10 p-5 backdrop-blur">
                            <form action="{{ route('products.index') }}">
                                <div class="flex flex-col gap-3 md:flex-row">
                                    <input class="by-input md:flex-1 bg-white/90" name="q" value="{{ request('q') }}" placeholder="{{ __('home.search_placeholder') }}" />
                                    <button class="by-btn-primary">{{ __('home.search') }}</button>
                                    <a class="by-btn-cta" href="{{ route('quote-requests.create', ['type' => 'physical_quote']) }}">{{ __('home.quote') }}</a>
                                </div>
                            </form>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a class="by-badge border-white/15 bg-white/10 text-white hover:bg-white/15" href="{{ route('products.index') }}">{{ __('home.products') }}</a>
                                <a class="by-badge border-white/15 bg-white/10 text-white hover:bg-white/15" href="{{ route('vendors.index') }}">{{ __('home.vendors') }}</a>
                                <a class="by-badge border-white/15 bg-white/10 text-white hover:bg-white/15" href="{{ route('service-requests.create') }}">{{ __('home.get_service') }}</a>
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
                            <a href="{{ route('quote-requests.create', ['type' => 'physical_quote']) }}" class="by-card border-white/15 bg-white/10 p-5 text-white backdrop-blur hover:bg-white/15">
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
                            <button type="button" class="h-2.5 w-2.5 rounded-full bg-white/40" data-dot></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="by-container -mt-2">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('products.index') }}" class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-slate-900 shadow-lg shadow-slate-900/10 transition hover:-translate-y-1 hover:shadow-xl">
                <img src="{{ asset('images/home/path-ready-products.png') }}" alt="{{ __('home.path_ready_title') }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">
                <div class="absolute inset-0 bg-linear-to-t from-slate-950 via-slate-950/55 to-indigo-900/25"></div>
                <div class="relative flex min-h-60 flex-col justify-end p-6 sm:min-h-70">
                    <span class="inline-flex w-fit rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white/90 backdrop-blur">{{ __('home.path_ready_eyebrow') }}</span>
                    <h3 class="mt-3 text-2xl font-extrabold tracking-tight text-white">{{ __('home.path_ready_title') }}</h3>
                    <p class="mt-2 max-w-sm text-sm leading-relaxed text-white/80">{{ __('home.path_ready_body') }}</p>
                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-cyan-200">{{ __('home.path_ready_cta') }} →</span>
                </div>
            </a>
            <a href="{{ route('quote-requests.create', ['type' => 'physical_quote']) }}" class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-slate-900 shadow-lg shadow-slate-900/10 transition hover:-translate-y-1 hover:shadow-xl">
                <img src="{{ asset('images/home/path-print-rfq.png') }}" alt="{{ __('home.path_quote_title') }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">
                <div class="absolute inset-0 bg-linear-to-t from-slate-950 via-orange-950/55 to-amber-700/20"></div>
                <div class="relative flex min-h-60 flex-col justify-end p-6 sm:min-h-70">
                    <span class="inline-flex w-fit rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white/90 backdrop-blur">{{ __('home.path_quote_eyebrow') }}</span>
                    <h3 class="mt-3 text-2xl font-extrabold tracking-tight text-white">{{ __('home.path_quote_title') }}</h3>
                    <p class="mt-2 max-w-sm text-sm leading-relaxed text-white/80">{{ __('home.path_quote_body') }}</p>
                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-orange-200">{{ __('home.path_quote_cta') }} →</span>
                </div>
            </a>
            <a href="{{ route('quote-requests.create', ['type' => 'tabela']) }}" class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-slate-900 shadow-lg shadow-slate-900/10 transition hover:-translate-y-1 hover:shadow-xl">
                <img src="{{ asset('images/home/path-tabela.png') }}" alt="{{ __('home.path_tabela_title') }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">
                <div class="absolute inset-0 bg-linear-to-t from-slate-950 via-rose-950/50 to-fuchsia-800/20"></div>
                <div class="relative flex min-h-60 flex-col justify-end p-6 sm:min-h-70">
                    <span class="inline-flex w-fit rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white/90 backdrop-blur">{{ __('home.path_tabela_eyebrow') }}</span>
                    <h3 class="mt-3 text-2xl font-extrabold tracking-tight text-white">{{ __('home.path_tabela_title') }}</h3>
                    <p class="mt-2 max-w-sm text-sm leading-relaxed text-white/80">{{ __('home.path_tabela_body') }}</p>
                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-rose-200">{{ __('home.path_tabela_cta') }} →</span>
                </div>
            </a>
            <a href="{{ route('service-requests.create') }}" class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-slate-900 shadow-lg shadow-slate-900/10 transition hover:-translate-y-1 hover:shadow-xl">
                <img src="{{ asset('images/home/path-freelancer.png') }}" alt="{{ __('home.path_freelancer_title') }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">
                <div class="absolute inset-0 bg-linear-to-t from-slate-950 via-emerald-950/50 to-teal-700/20"></div>
                <div class="relative flex min-h-60 flex-col justify-end p-6 sm:min-h-70">
                    <span class="inline-flex w-fit rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white/90 backdrop-blur">{{ __('home.path_freelancer_eyebrow') }}</span>
                    <h3 class="mt-3 text-2xl font-extrabold tracking-tight text-white">{{ __('home.path_freelancer_title') }}</h3>
                    <p class="mt-2 max-w-sm text-sm leading-relaxed text-white/80">{{ __('home.path_freelancer_body') }}</p>
                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-emerald-200">{{ __('home.path_freelancer_cta') }} →</span>
                </div>
            </a>
        </div>
    </section>

    <section class="by-container py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('home.collection_eyebrow') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ __('home.collection_title') }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ __('home.collection_body') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="by-btn-secondary">{{ __('home.all_products') }}</a>
        </div>
            @if($categories->isEmpty())
                <div class="mt-6 by-card p-8 text-center">
                    <p class="text-sm text-slate-600">{{ __('home.no_categories') }}</p>
                </div>
            @else
                <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                    @foreach($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="group by-card by-card-hover overflow-hidden">
                            <div class="aspect-4/3 bg-slate-100">
                                <img src="https://picsum.photos/900/700?random=kategori{{ $category->id }}" alt="{{ $category->localizedName() }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                            </div>
                            <div class="p-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $category->localizedName() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
    </section>

    <section class="by-container py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('home.service_eyebrow') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ __('home.service_title') }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ __('home.service_body') }}</p>
            </div>
            <a href="{{ route('service-requests.index') }}" class="by-btn-secondary">{{ __('home.all_jobs') }}</a>
        </div>

            <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
                @foreach($freelancerCategories ?? [] as $cat)
                    <div class="by-card by-card-hover overflow-hidden">
                        <a href="{{ route('service-requests.index', ['category' => $cat['key']]) }}" class="block">
                            <div class="aspect-4/3 bg-slate-100">
                                <img src="https://picsum.photos/900/700?random={{ $cat['seed'] }}" alt="{{ $cat['label'] }}" class="h-full w-full object-cover">
                            </div>
                            <div class="p-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $cat['label'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ __('home.open_jobs', ['count' => $cat['count']]) }}</p>
                            </div>
                        </a>
                        <div class="p-4 pt-0">
                            <a href="{{ route('service-requests.create', ['category' => $cat['key']]) }}" class="w-full by-btn-primary">{{ __('home.path_freelancer_cta') }}</a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(isset($freelancerJobs) && $freelancerJobs->isNotEmpty())
                <div class="mt-10 by-card p-6">
                    <p class="text-sm font-bold text-slate-900">{{ __('home.latest_jobs') }}</p>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        @foreach($freelancerJobs->take(3) as $job)
                            <a href="{{ route('service-requests.show', $job) }}" class="rounded-2xl border border-slate-200 bg-white/70 p-4 hover:bg-white">
                                <p class="text-sm font-semibold text-slate-900">{{ Str::limit($job->title, 54) }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ \App\Support\FreelancerCategories::label($job->category) }} · ₺{{ $job->budget_min ? number_format($job->budget_min, 0, ',', '.') : '?' }}+</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
    </section>

    <section class="by-container py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('home.featured_eyebrow') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ __('home.featured_title') }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ __('home.featured_body') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="by-btn-secondary">{{ __('home.see_all_products') }}</a>
        </div>

        @php
            $displayDiscovery = isset($discoveryProducts) && $discoveryProducts->isNotEmpty() ? $discoveryProducts : $featuredProducts;
        @endphp

        @if($displayDiscovery->isEmpty())
            <div class="mt-6 by-card p-8 text-center">
                <p class="text-sm text-slate-600">{{ __('home.no_products') }}</p>
            </div>
        @else
            <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                @foreach($displayDiscovery as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="by-container py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('home.digital_eyebrow') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ __('home.digital_title') }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ __('home.digital_body') }}</p>
            </div>
            <a href="{{ route('products.index', ['type' => 'digital']) }}" class="by-btn-secondary">{{ __('home.see_all') }}</a>
        </div>
            @if(isset($digitalProducts) && $digitalProducts->isNotEmpty())
                <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                    @foreach($digitalProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            @else
                <div class="mt-6 by-card p-8 text-center">
                    <p class="text-sm text-slate-600">{{ __('home.digital_empty') }}</p>
                    <a href="{{ route('products.index', ['type' => 'digital']) }}" class="mt-4 inline-flex by-btn-secondary">{{ __('home.digital_browse') }}</a>
                </div>
            @endif
    </section>

@endsection

