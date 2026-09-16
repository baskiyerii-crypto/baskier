@extends('layouts.app')

@section('title', __('ui.products_title'))

@section('content')
    <div class="by-container py-6">
        <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="by-card by-card-hover overflow-hidden">
                <div class="relative">
                    <div class="h-28 bg-linear-to-br from-orange-400/20 via-white to-indigo-500/10"></div>
                    <div class="absolute inset-x-0 top-0 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('ui.filter') }}</p>
                        <h2 class="mt-1 text-lg font-bold tracking-tight text-slate-900">{{ __('ui.categories') }}</h2>
                    </div>
                </div>
                <div class="p-5 pt-3">
                    @if($categories->isEmpty())
                        <p class="text-sm text-slate-500">{{ __('ui.no_categories') }}</p>
                    @else
                        <div class="space-y-1">
                            <a href="{{ route('products.index') }}"
                               class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold {{ request('category_id') ? 'text-slate-600 hover:bg-slate-50' : 'bg-orange-50 text-orange-900 border border-orange-200' }}">
                                <span>{{ __('ui.all') }}</span>
                                <span class="by-badge">→</span>
                            </a>
                            @foreach($categories as $category)
                                <a href="{{ route('products.index', array_merge(request()->only('q', 'type'), ['category_id' => $category->id])) }}"
                                   class="flex items-center justify-between rounded-xl px-3 py-2 text-sm {{ (int) request('category_id') === $category->id ? 'bg-orange-50 text-orange-900 border border-orange-200 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                                    <span class="truncate">{{ $category->name }}</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                @foreach($category->children as $child)
                                    <a href="{{ route('products.index', array_merge(request()->only('q', 'type'), ['category_id' => $child->id])) }}"
                                       class="ml-3 flex items-center justify-between rounded-xl px-3 py-2 text-sm {{ (int) request('category_id') === $child->id ? 'bg-orange-50 text-orange-900 border border-orange-200 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <span class="truncate">{{ $child->name }}</span>
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                    @endif
                </div>
            </aside>

            <section class="min-w-0">
                <div class="by-card p-5 md:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('ui.catalog') }}</p>
                            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                                {{ request('type') === 'digital' ? __('ui.digital_products') : __('ui.products') }}
                            </h1>
                            @if(request('q'))
                                <p class="mt-1 text-sm text-slate-600">{{ __('ui.search_results_for', ['q' => request('q')]) }}</p>
                            @endif
                        </div>

                        <form class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row" action="{{ route('products.index') }}">
                            @if(request('category_id'))
                                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                            @endif
                            @if(request('type'))
                                <input type="hidden" name="type" value="{{ request('type') }}">
                            @endif
                            <input
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="{{ __('ui.search_placeholder') }}"
                                class="w-full rounded-full border border-slate-200 bg-white/80 px-4 py-2.5 text-sm outline-none ring-orange-400 focus:ring-2 sm:w-85"
                            />
                            <button class="by-btn-primary" type="submit">{{ __('ui.search') }}</button>
                        </form>
                    </div>
                </div>

                <div class="mt-6">
                    @if($products->isEmpty())
                        <div class="by-card p-8 text-center">
                            <p class="text-sm text-slate-600">{{ __('ui.no_products') }}</p>
                            <a href="{{ route('products.index') }}" class="mt-4 inline-flex by-btn-secondary">{{ __('ui.clear_filters') }}</a>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                            @foreach($products as $product)
                                <a href="{{ route('products.show', $product->slug) }}" class="group by-card by-card-hover overflow-hidden">
                                    <div class="aspect-4/3 bg-slate-100">
                                        @if($product->main_image)
                                            <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                                        @else
                                            <div class="h-full flex items-center justify-center text-sm text-slate-500">Ürün görseli hazırlanıyor</div>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $product->category?->name ?? __('ui.category') }}</p>
                                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $product->name }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $product->vendor?->name ?? __('ui.vendor') }}</p>
                                            </div>
                                            <span class="rounded-full bg-orange-50 px-3 py-1 text-sm font-bold text-orange-900">
                                                ₺{{ number_format($product->price, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
