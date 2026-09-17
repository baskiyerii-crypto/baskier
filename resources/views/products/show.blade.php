@extends('layouts.app')

@php
    $crumbs = [
        ['title' => 'Ürünler', 'url' => route('products.index')],
    ];
    if (isset($categoryTrail)) {
        foreach($categoryTrail as $trail) {
            $crumbs[] = ['title' => $trail->name, 'url' => route('products.index', ['category' => $trail->slug ?? $trail->id])];
        }
    }
    $crumbs[] = ['title' => $product->localizedName(), 'url' => ''];

    $reviewAvg = $productReviewStats->avg_rating ?? null;
    $reviewCount = (int) ($productReviewStats->reviews_count ?? 0);
    $isQuote = $product->isQuoteBased();
@endphp

@section('title', $product->localizedName())
@section('meta_description', \App\Support\SeoHelper::description($product->short_description ?? $product->description))
@section('canonical', route('products.show', $product->slug))

@push('head')
<script type="application/ld+json">
{!! json_encode(\App\Support\SeoHelper::productJsonLd($product), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
    <div class="by-container py-6">
        <x-breadcrumb :items="$crumbs" />

        {{-- Main Product Showcase Grid --}}
        <div class="grid gap-8 lg:grid-cols-12 items-start mt-4">
            {{-- Product Visual (7 cols on lg) --}}
            <div class="lg:col-span-7">
                <div class="rounded-2xl border border-[#DEDAD2] bg-white p-2 shadow-xs overflow-hidden">
                    <div class="relative aspect-4/3 sm:aspect-16/10 bg-[#F7F5F0] rounded-xl overflow-hidden flex items-center justify-center">
                        @if($product->main_image)
                            <img 
                                src="{{ asset('storage/'.$product->main_image) }}" 
                                alt="{{ $product->localizedName() }}" 
                                width="800"
                                height="600"
                                fetchpriority="high"
                                class="h-full w-full object-cover"
                            />
                        @else
                            <img 
                                src="{{ asset('images/placeholder-product.svg') }}" 
                                alt="{{ $product->localizedName() }}" 
                                width="800"
                                height="600"
                                fetchpriority="high"
                                class="h-full w-full object-contain p-8"
                            />
                        @endif

                        @if($product->stock <= 0 && !$isQuote)
                            <div class="absolute top-4 left-4 rounded-md bg-[#182023]/90 px-3 py-1 text-xs font-bold text-white backdrop-blur-xs">
                                Stok Tükendi
                            </div>
                        @elseif($isQuote)
                            <div class="absolute top-4 left-4 rounded-md bg-[#C2410C] px-3 py-1 text-xs font-bold text-white shadow-xs">
                                Teklif Usulü Üretim
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Product Descriptions Tab / Body --}}
                <div class="mt-8 rounded-xl border border-[#DEDAD2] bg-white p-6 shadow-xs">
                    <h2 class="text-base font-bold text-[#182023] border-b border-[#DEDAD2] pb-3 mb-4">
                        Ürün Açıklaması ve Özellikleri
                    </h2>

                    @if($product->localized('short_description'))
                        <p class="text-sm font-medium text-[#182023] leading-relaxed mb-4">
                            {{ $product->localized('short_description') }}
                        </p>
                    @endif

                    @if($product->localized('description'))
                        <div class="prose prose-sm max-w-none text-sm text-[#596166] leading-relaxed whitespace-pre-wrap">
                            {{ $product->localized('description') }}
                        </div>
                    @else
                        <p class="text-xs text-[#596166] italic">Bu ürün için ayrıntılı açıklama girilmemiş.</p>
                    @endif
                </div>
            </div>

            {{-- Purchase & Vendor Card (5 cols on lg, sticky) --}}
            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
                <div class="rounded-xl border border-[#DEDAD2] bg-white p-6 shadow-xs">
                    {{-- Category & Title --}}
                    <div class="mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#596166]">
                            {{ $product->category?->localizedName() ?? 'Genel Kategori' }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#182023] tracking-tight leading-snug">
                        {{ $product->localizedName() }}
                    </h1>

                    {{-- Review Stars Summary --}}
                    @if($reviewCount > 0)
                        <div class="mt-3 flex items-center gap-2">
                            <div class="flex text-amber-500 text-sm">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= (int) round((float) $reviewAvg) ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            <span class="text-xs font-bold text-[#182023]">{{ number_format((float) $reviewAvg, 1, ',', '.') }}</span>
                            <span class="text-xs text-[#596166]">({{ $reviewCount }} değerlendirme)</span>
                        </div>
                    @endif

                    {{-- Price & Stock Row --}}
                    <div class="mt-5 pt-4 border-t border-[#DEDAD2] flex flex-wrap items-baseline justify-between gap-3">
                        @if($isQuote)
                            <div>
                                <span class="text-xl sm:text-2xl font-extrabold text-[#182023]">Teklif Alınız</span>
                                @if($product->price_min && $product->price_max)
                                    <p class="text-xs text-[#596166] mt-0.5">
                                        Tahmini: ₺{{ number_format((float)$product->price_min, 0, ',', '.') }} - ₺{{ number_format((float)$product->price_max, 0, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                        @else
                            <div class="flex items-baseline gap-2">
                                <span class="text-2xl sm:text-3xl font-extrabold text-[#182023]" id="product-display-price" data-base-price="{{ (float) $product->price }}">
                                    ₺{{ number_format($product->price, 2, ',', '.') }}
                                </span>
                                <span class="text-xs text-[#596166] font-medium">+KDV</span>
                            </div>

                            <span id="product-display-stock" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold {{ $product->stock > 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}" data-base-stock="{{ (int) $product->stock }}">
                                {{ $product->stock > 0 ? 'Stokta (' . $product->stock . ' adet)' : 'Tükendi' }}
                            </span>
                        @endif
                    </div>

                    {{-- Vendor Row with Trust Badge --}}
                    @if($product->vendor)
                        <div class="mt-5 rounded-lg border border-[#DEDAD2] bg-[#F7F5F0]/60 p-3.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold text-[#596166]">Üretici & Satıcı</p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <a href="{{ route('vendors.show', $product->vendor->slug) }}" class="text-sm font-bold text-[#182023] hover:text-[#C2410C] truncate">
                                        {{ $product->vendor->name }}
                                    </a>
                                    <x-trust-badge :vendor="$product->vendor" size="sm" />
                                </div>
                            </div>
                            <a href="{{ route('vendors.show', $product->vendor->slug) }}" class="shrink-0 text-xs font-semibold text-[#596166] hover:text-[#182023]">
                                Profil →
                            </a>
                        </div>
                    @endif

                    {{-- In-Cart Notice --}}
                    @auth
                        @if($inCartQuantity > 0)
                            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50/80 p-3 text-xs text-emerald-900 flex items-center justify-between">
                                <span>Sepetinizde bu üründen <strong>{{ $inCartQuantity }} adet</strong> bulunuyor.</span>
                                <a href="{{ route('cart.index') }}" class="font-bold underline ml-2">Sepete Git</a>
                            </div>
                        @endif
                    @endauth

                    {{-- Purchase Form / Quote Action --}}
                    <div class="mt-6 pt-4 border-t border-[#DEDAD2]">
                        @if($isQuote)
                            <div class="space-y-3">
                                <x-button href="{{ route('quote-requests.create', ['product_id' => $product->id, 'type' => 'physical_quote']) }}" variant="primary" fullWidth size="lg">
                                    Hizmet Teklifi Al
                                </x-button>
                                <p class="text-[11px] text-[#596166] text-center">Özel ölçü, malzeme ve adet tercihlerinizi belirterek satıcılardan teklif toplayın.</p>
                            </div>
                        @else
                            @auth
                                @if($product->stock > 0)
                                    <form id="product-buy-form" action="{{ route('cart.add', $product) }}" method="post" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="buy_now" id="buy_now_flag" value="0">

                                        @if($product->variants->isNotEmpty())
                                            <div>
                                                <label class="block text-xs font-semibold text-[#182023] mb-1.5" for="variant-selector">Seçenek / Varyant</label>
                                                <select name="variant_id" id="variant-selector" class="w-full min-h-[44px] rounded-lg border border-[#DEDAD2] bg-white px-3 py-2 text-sm text-[#182023] outline-none focus:border-[#C2410C]">
                                                    @foreach($product->variants as $variant)
                                                        @php $variantPrice = (float) $product->price + (float) $variant->price_adjustment; @endphp
                                                        <option value="{{ $variant->id }}"
                                                                data-price="{{ $variantPrice }}"
                                                                data-stock="{{ (int) $variant->stock }}"
                                                                @selected($loop->first)>
                                                            {{ $variant->name }} — ₺{{ number_format($variantPrice, 2, ',', '.') }} (Stok: {{ $variant->stock }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <div class="flex items-end gap-3">
                                            <div class="w-28 shrink-0">
                                                <label class="block text-xs font-semibold text-[#182023] mb-1.5" for="product-qty">Adet</label>
                                                <input 
                                                    id="product-qty" 
                                                    type="number" 
                                                    name="quantity" 
                                                    value="1" 
                                                    min="1" 
                                                    max="{{ $product->variants->first()?->stock ?? $product->stock }}"
                                                    class="w-full min-h-[44px] rounded-lg border border-[#DEDAD2] bg-white px-3 text-center text-sm font-bold text-[#182023] outline-none focus:border-[#C2410C]"
                                                />
                                            </div>
                                            <div class="flex-1">
                                                <button type="submit" class="w-full min-h-[44px] rounded-lg bg-[#C2410C] text-sm font-bold text-white shadow-xs hover:bg-[#9A3412] transition" onclick="document.getElementById('buy_now_flag').value='0'">
                                                    Sepete Ekle
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="mt-3 flex gap-2">
                                        <button type="button" id="btn-buy-now" class="w-full min-h-[44px] rounded-lg border border-[#DEDAD2] bg-white text-xs font-bold text-[#182023] hover:bg-[#F7F5F0] transition" onclick="document.getElementById('buy_now_flag').value='1'; document.getElementById('product-buy-form').submit();">
                                            Hemen Satın Al
                                        </button>
                                    </div>
                                @else
                                    <button type="button" disabled class="w-full min-h-[48px] rounded-lg bg-slate-100 text-xs font-bold text-[#596166]/60 cursor-not-allowed border border-[#DEDAD2]">
                                        Ürün Tükendi
                                    </button>
                                @endif

                                <div class="mt-3 pt-3 border-t border-[#DEDAD2]">
                                    <form action="{{ route('favorites.toggle', $product) }}" method="post">
                                        @csrf
                                        <button type="submit" class="w-full text-center text-xs font-semibold text-[#596166] hover:text-[#182023] py-1 transition flex items-center justify-center gap-1.5">
                                            <span>{{ $isFavorited ? '♥ Favorilerimde Kayıtlı' : '♡ Favorilere Ekle' }}</span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="rounded-lg border border-[#DEDAD2] bg-[#F7F5F0] p-4 text-center">
                                    <p class="text-xs text-[#596166] mb-3">Bu ürünü satın almak veya sepete eklemek için giriş yapmanız gerekmektedir.</p>
                                    <x-button href="{{ route('login') }}" variant="primary" fullWidth size="md">
                                        Giriş Yap ve Satın Al
                                    </x-button>
                                </div>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer Reviews --}}
        <div class="mt-12 rounded-xl border border-[#DEDAD2] bg-white p-6 sm:p-8 shadow-xs">
            <h2 class="text-lg font-bold text-[#182023] tracking-tight">Değerlendirmeler ve Yorumlar</h2>
            @if($reviewCount > 0)
                <div class="mt-3 flex items-center gap-3">
                    <div class="flex text-amber-500 text-lg">
                        @for($i = 1; $i <= 5; $i++)
                            <span>{{ $i <= (int) round((float) $reviewAvg) ? '★' : '☆' }}</span>
                        @endfor
                    </div>
                    <span class="text-sm font-bold text-[#182023]">{{ number_format((float) $reviewAvg, 1, ',', '.') }} / 5</span>
                    <span class="text-xs text-[#596166]">— {{ $reviewCount }} doğrulanmış müşteri değerlendirmesi</span>
                </div>
            @else
                <p class="mt-2 text-xs text-[#596166]">Bu ürün için henüz değerlendirme yapılmamış.</p>
            @endif

            @if($productReviews->isNotEmpty())
                <ul class="mt-6 space-y-3 border-t border-[#DEDAD2] pt-6">
                    @foreach($productReviews as $rev)
                        <li class="rounded-lg border border-[#DEDAD2] bg-[#F7F5F0]/40 p-4">
                            <div class="flex items-center justify-between text-xs text-[#596166]">
                                <span class="font-bold text-[#182023]">{{ $rev->user?->name ?? 'Müşteri' }}</span>
                                <time datetime="{{ $rev->created_at?->toIso8601String() }}">{{ $rev->created_at?->translatedFormat('d M Y') }}</time>
                            </div>
                            <div class="mt-1 text-amber-500 text-xs">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $rev->rating ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            @if($rev->comment)
                                <p class="mt-2 text-xs text-[#182023] leading-relaxed">{{ $rev->comment }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Related Products --}}
        @if($related->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-xl font-bold text-[#182023] tracking-tight mb-4">Benzer Ürünler</h2>
                <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                    @foreach($related as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const variantSelect = document.getElementById('variant-selector');
            const priceEl = document.getElementById('product-display-price');
            const stockEl = document.getElementById('product-display-stock');
            const qtyInput = document.getElementById('product-qty');
            const submitBtn = document.querySelector('#product-buy-form button[type="submit"]');
            const buyNowBtn = document.getElementById('btn-buy-now');

            function formatMoney(amount) {
                return '₺' + Number(amount).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function updateVariant() {
                if (!variantSelect) return;
                const opt = variantSelect.selectedOptions[0];
                if (!opt) return;

                const price = parseFloat(opt.dataset.price);
                const stock = parseInt(opt.dataset.stock, 10);

                if (priceEl && !isNaN(price)) {
                    priceEl.textContent = formatMoney(price);
                }

                if (stockEl && !isNaN(stock)) {
                    if (stock > 0) {
                        stockEl.className = 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200';
                        stockEl.textContent = 'Stokta (' + stock + ' adet)';
                        if (qtyInput) qtyInput.max = stock;
                        if (submitBtn) submitBtn.disabled = false;
                        if (buyNowBtn) buyNowBtn.disabled = false;
                    } else {
                        stockEl.className = 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200';
                        stockEl.textContent = 'Stok tükendi';
                        if (submitBtn) submitBtn.disabled = true;
                        if (buyNowBtn) buyNowBtn.disabled = true;
                    }
                }
            }

            if (variantSelect) {
                variantSelect.addEventListener('change', updateVariant);
                updateVariant();
            }
        });
    </script>
@endsection
