@extends('layouts.app')

@section('title', $product->name . ' - BaskıYeri Pazaryeri')

@section('content')
    <div class="by-container py-6">
        <nav class="mb-5 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-slate-900">Anasayfa</a>
            <span class="mx-2">/</span>
            <a href="{{ route('products.index') }}" class="hover:text-slate-900">Ürünler</a>
            @foreach($categoryTrail as $trail)
                <span class="mx-2">/</span>
                <a href="{{ route('products.index', ['category_id' => $trail->id]) }}" class="hover:text-slate-900">{{ $trail->name }}</a>
            @endforeach
            <span class="mx-2">/</span>
            <span class="text-slate-700">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="by-card overflow-hidden">
                <div class="aspect-[4/3] bg-slate-100">
                    @if($product->main_image)
                        <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                        <img src="https://picsum.photos/1200/900?random=detay{{ $product->id }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="by-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $product->category?->name ?? 'Kategori' }}</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $product->name }}</h1>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-orange-50 px-4 py-2 text-xl font-extrabold text-orange-900">
                            ₺{{ number_format($product->price, 2, ',', '.') }}
                        </span>
                        @if($product->stock > 0)
                            <span class="by-badge border-emerald-200 bg-emerald-50 text-emerald-800">Stokta</span>
                        @else
                            <span class="by-badge">Stok yok</span>
                        @endif
                    </div>

                    @if($product->vendor)
                        <div class="mt-5 rounded-2xl border border-slate-200 bg-white/70 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Satıcı</p>
                            <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                                <a href="{{ route('vendors.show', $product->vendor->slug) }}" class="text-sm font-semibold text-slate-900 hover:underline">
                                    {{ $product->vendor->name }}
                                </a>
                                <a href="{{ route('vendors.show', $product->vendor->slug) }}" class="by-btn-secondary">Profili gör</a>
                            </div>
                        </div>
                    @endif

                    @if($product->short_description)
                        <p class="mt-5 text-sm leading-relaxed text-slate-700">{{ $product->short_description }}</p>
                    @endif
                    @if($product->description)
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Açıklama</p>
                            <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $product->description }}</p>
                        </div>
                    @endif
                </div>

                <div class="by-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Satın alma</p>
                    @auth
                        @if($inCartQuantity > 0)
                            <div class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-emerald-900">Sepette {{ $inCartQuantity }} adet var.</p>
                                    <a href="{{ route('cart.index') }}" class="by-btn-secondary border-emerald-200 bg-white/70">Sepete git</a>
                                </div>
                            </div>
                        @endif

                        @if($product->stock > 0)
                            <form id="product-buy-form" action="{{ route('cart.add', $product) }}" method="post" class="mt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="buy_now" id="buy_now_flag" value="0">
                                @if($product->variants->isNotEmpty())
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600">Varyant</label>
                                        <select name="variant_id" class="by-input mt-1 w-full">
                                            @foreach($product->variants as $variant)
                                                @php $variantPrice = (float) $product->price + (float) $variant->price_adjustment; @endphp
                                                <option value="{{ $variant->id }}">
                                                    {{ $variant->name }} — ₺{{ number_format($variantPrice, 2, ',', '.') }} (Stok: {{ $variant->stock }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                    <div class="w-full sm:w-28 shrink-0">
                                        <label class="text-xs font-semibold text-slate-600" for="product-qty">Adet</label>
                                        <input id="product-qty" type="number" name="quantity" value="1" min="1" max="999"
                                               class="by-input mt-1 w-full min-h-[48px]">
                                    </div>
                                    <div class="flex-1">
                                        <button type="submit" class="w-full by-btn-primary min-h-[48px]" onclick="document.getElementById('buy_now_flag').value='0'">Sepete ekle</button>
                                    </div>
                                </div>
                            </form>
                        @endif

                        <div class="mt-4 flex flex-wrap gap-2">
                            <form action="{{ route('favorites.toggle', $product) }}" method="post">
                                @csrf
                                <button type="submit" class="by-btn-secondary">
                                    {{ $isFavorited ? '♥ Favoride' : '♡ Favorilere ekle' }}
                                </button>
                            </form>
                            @if($product->stock > 0)
                                <button type="button" class="by-btn-secondary" onclick="document.getElementById('buy_now_flag').value='1'; document.getElementById('product-buy-form').submit();">Hızlı satın al</button>
                            @endif
                        </div>
                    @else
                        <p class="mt-3 text-sm text-slate-600">
                            Sepete eklemek için <a class="font-semibold text-orange-600 hover:underline" href="{{ route('login') }}">giriş yapın</a>.
                        </p>
                    @endauth
                </div>
            </div>
        </div>

    @php
        $reviewAvg = $productReviewStats->avg_rating ?? null;
        $reviewCount = (int) ($productReviewStats->reviews_count ?? 0);
    @endphp
    <div class="mt-10 by-card p-6">
        <h2 class="text-lg font-bold tracking-tight text-slate-900">Yorumlar ve puanlama</h2>
        @if($reviewCount > 0)
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-1" aria-label="Ortalama puan {{ $reviewAvg }} üzerinden 5">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= (int) round((float) $reviewAvg) ? 'text-amber-500' : 'text-slate-300' }}" style="font-size:1.25rem;line-height:1;">★</span>
                    @endfor
                </div>
                <div class="text-sm text-slate-700">
                    <span class="font-semibold">{{ number_format((float) $reviewAvg, 1, ',', '.') }}</span>
                    <span class="text-slate-500">/ 5</span>
                    <span class="text-slate-500">— {{ $reviewCount }} değerlendirme</span>
                </div>
            </div>
        @else
            <p class="mt-2 text-sm text-slate-500">Bu ürün için henüz değerlendirme yok.</p>
        @endif

        @auth
            <p class="mt-3 text-sm text-slate-600">
                Satın aldığınız ürünü sipariş tesliminden sonra
                <a href="{{ route('account.orders.index') }}">Siparişlerim</a>
                üzerinden puanlayabilirsiniz; sipariş başına tek değerlendirme kaydedilir.
            </p>
        @else
            <p class="mt-3 text-sm text-slate-600">Değerlendirme yapmak için <a class="font-semibold text-orange-600 hover:underline" href="{{ route('login') }}">giriş yapın</a> ve teslim edilen siparişinizi açın.</p>
        @endauth

        @if($productReviews->isNotEmpty())
            <ul class="mt-6 space-y-3 border-t border-slate-200 pt-6">
                @foreach($productReviews as $rev)
                    <li class="rounded-2xl border border-slate-200 bg-white/70 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500">
                            <span class="font-semibold text-slate-900">{{ $rev->user?->name ?? 'Müşteri' }}</span>
                            <time datetime="{{ $rev->created_at?->toIso8601String() }}">{{ $rev->created_at?->translatedFormat('d M Y') }}</time>
                        </div>
                        <div class="mt-2">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= $rev->rating ? 'text-amber-500' : 'text-slate-300' }}">★</span>
                            @endfor
                        </div>
                        @if($rev->comment)
                            <p class="mt-2 text-sm text-slate-700">{{ $rev->comment }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if($alsoBought->isNotEmpty())
        <div class="mt-10">
            <h2 class="text-lg font-bold tracking-tight text-slate-900">Beraber alınan ürünler</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($alsoBought->take(4) as $item)
                    <a href="{{ route('products.show', $item->slug) }}" class="group by-card by-card-hover overflow-hidden">
                        <div class="aspect-[4/3] bg-slate-100">
                            @if($item->main_image)
                                <img src="{{ asset('storage/'.$item->main_image) }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                            @else
                                <img src="https://picsum.photos/800/600?random=beraber{{ $item->id }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                            @endif
                        </div>
                        <div class="p-4">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $item->name }}</p>
                            <p class="mt-1 text-sm font-bold text-orange-900">₺{{ number_format($item->price, 2, ',', '.') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($related->isNotEmpty())
        <div class="mt-10">
            <h2 class="text-lg font-bold tracking-tight text-slate-900">{{ $alsoBought->isNotEmpty() ? 'Benzer ürünler' : 'Size önerilen benzer ürünler' }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($related as $item)
                    <a href="{{ route('products.show', $item->slug) }}" class="group by-card by-card-hover overflow-hidden">
                        <div class="aspect-[4/3] bg-slate-100">
                            @if($item->main_image)
                                <img src="{{ asset('storage/'.$item->main_image) }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                            @else
                                <img src="https://picsum.photos/800/600?random=benzer{{ $item->id }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                            @endif
                        </div>
                        <div class="p-4">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $item->name }}</p>
                            <p class="mt-1 text-sm font-bold text-orange-900">₺{{ number_format($item->price, 2, ',', '.') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
    </div>
@endsection

