@extends('layouts.app')

@section('title', 'Ürünler - BaskıYeri Pazaryeri')

@section('content')
    <div class="by-container py-6">
        <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
            <aside class="by-card by-card-hover overflow-hidden">
                <div class="relative">
                    <div class="h-28 bg-gradient-to-br from-orange-400/20 via-white to-indigo-500/10"></div>
                    <div class="absolute inset-x-0 top-0 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Filtre</p>
                        <h2 class="mt-1 text-lg font-bold tracking-tight text-slate-900">Kategoriler</h2>
                    </div>
                </div>
                <div class="p-5 pt-3">
                    @if($categories->isEmpty())
                        <p class="text-sm text-slate-500">Kategori bulunamadı.</p>
                    @else
                        <div class="space-y-1">
                            <a href="{{ route('products.index') }}"
                               class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold {{ request('category_id') ? 'text-slate-600 hover:bg-slate-50' : 'bg-orange-50 text-orange-900 border border-orange-200' }}">
                                <span>Tümü</span>
                                <span class="by-badge">→</span>
                            </a>
                            @foreach($categories as $category)
                                <a href="{{ route('products.index', ['category_id' => $category->id]) }}"
                                   class="flex items-center justify-between rounded-xl px-3 py-2 text-sm {{ (int) request('category_id') === $category->id ? 'bg-orange-50 text-orange-900 border border-orange-200 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                                    <span class="truncate">{{ $category->name }}</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                @foreach($category->children as $child)
                                    <a href="{{ route('products.index', ['category_id' => $child->id]) }}"
                                       class="ml-3 flex items-center justify-between rounded-xl px-3 py-2 text-sm {{ (int) request('category_id') === $child->id ? 'bg-orange-50 text-orange-900 border border-orange-200 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <span class="truncate">{{ $child->name }}</span>
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                    @endif
                </div>
            </aside>

            <section>
                <div class="by-card p-5 md:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Ürün kataloğu</p>
                            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                                {{ request('type') === 'digital' ? 'Dijital ürünler' : 'Ürünler' }}
                            </h1>
                            @if(request('q'))
                                <p class="mt-1 text-sm text-slate-600">“{{ request('q') }}” için sonuçlar</p>
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
                                placeholder="Ürün ara (kartvizit, broşür...)"
                                class="w-full rounded-full border border-slate-200 bg-white/80 px-4 py-2.5 text-sm outline-none ring-orange-400 focus:ring-2 sm:w-[340px]"
                            />
                            <button class="by-btn-primary" type="submit">Ara</button>
                        </form>
                    </div>
                </div>

                <div class="mt-6">
                    @if($products->isEmpty())
                        <div class="by-card p-8 text-center">
                            <p class="text-sm text-slate-600">Ürün bulunamadı.</p>
                            <a href="{{ route('products.index') }}" class="mt-4 inline-flex by-btn-secondary">Filtreleri temizle</a>
                        </div>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($products as $product)
                                <a href="{{ route('products.show', $product->slug) }}" class="group by-card by-card-hover overflow-hidden">
                                    <div class="aspect-[4/3] bg-slate-100">
                                        @if($product->main_image)
                                            <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                                        @else
                                            <img src="https://picsum.photos/800/600?random=liste{{ $product->id ?? $loop->index }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $product->category?->name ?? 'Kategori' }}</p>
                                                <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $product->name }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $product->vendor?->name ?? 'Satıcı' }}</p>
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

