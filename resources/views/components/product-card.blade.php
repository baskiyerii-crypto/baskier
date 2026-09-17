@props(['product'])

@php
    $isQuote = $product->isQuoteBased();
    $hasVariants = $product->hasVariants();
    $isOutOfStock = ! $isQuote && ((int) $product->stock <= 0);
    $formattedPrice = number_format((float) $product->price, 2, ',', '.');
    $categoryName = $product->category?->localizedName() ?? __('ui.category');
    $vendorName = $product->vendor?->name ?? __('home.vendor_fallback');
@endphp

<div class="by-card by-card-hover flex flex-col justify-between overflow-hidden bg-white group product-card" data-product-id="{{ $product->id }}">
    <div>
        {{-- Product Image (Links to Detail) --}}
        <a href="{{ route('products.show', $product->slug) }}" class="relative block aspect-4/3 bg-slate-100 overflow-hidden" title="{{ $product->localizedName() }}">
            @if($product->main_image)
                <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->localizedName() }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" loading="lazy">
            @else
                <img src="https://picsum.photos/900/700?random=urun{{ $product->id }}" alt="{{ $product->localizedName() }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" loading="lazy">
            @endif

            @if($isOutOfStock)
                <span class="absolute top-2.5 left-2.5 rounded-full bg-slate-900/80 px-2.5 py-0.5 text-xs font-semibold text-white backdrop-blur">
                    {{ __('ui.out_of_stock') ?? 'Tükendi' }}
                </span>
            @elseif($isQuote)
                <span class="absolute top-2.5 left-2.5 rounded-full bg-indigo-600/90 px-2.5 py-0.5 text-xs font-semibold text-white backdrop-blur">
                    Teklifli Ürün
                </span>
            @elseif($product->isDigital())
                <span class="absolute top-2.5 left-2.5 rounded-full bg-emerald-600/90 px-2.5 py-0.5 text-xs font-semibold text-white backdrop-blur">
                    Dijital
                </span>
            @endif
        </a>

        {{-- Meta & Title --}}
        <div class="p-4 pb-2">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span class="font-semibold uppercase tracking-wider truncate max-w-[50%]">{{ $categoryName }}</span>
                <span class="truncate max-w-[45%] text-slate-400">{{ $vendorName }}</span>
            </div>

            <a href="{{ route('products.show', $product->slug) }}" class="block font-semibold text-slate-900 line-clamp-2 hover:text-orange-600 transition-colors" title="{{ $product->localizedName() }}">
                {{ $product->localizedName() }}
            </a>
        </div>
    </div>

    {{-- Price & CTA Container --}}
    <div class="p-4 pt-2 mt-auto">
        <div class="mb-3 flex items-baseline justify-between">
            @if($isQuote)
                <span class="text-sm font-bold text-slate-700">Teklif Alınız</span>
                @if($product->price_min && $product->price_max)
                    <span class="text-xs text-slate-500">₺{{ number_format((float)$product->price_min, 0, ',', '.') }} - ₺{{ number_format((float)$product->price_max, 0, ',', '.') }}</span>
                @endif
            @else
                <span class="text-base font-extrabold text-slate-900">₺{{ $formattedPrice }}</span>
                @if($hasVariants)
                    <span class="text-[11px] font-medium text-slate-500">Varyantlı</span>
                @endif
            @endif
        </div>

        {{-- Action Buttons --}}
        <div>
            @if($isQuote)
                <a href="{{ route('quote-requests.create', ['product_id' => $product->id, 'type' => 'physical_quote']) }}" class="by-btn-secondary w-full text-center py-2 text-xs font-bold block">
                    Hizmet Teklifi Al
                </a>
            @elseif($isOutOfStock)
                <button type="button" disabled class="w-full rounded-xl bg-slate-100 py-2 text-xs font-bold text-slate-400 cursor-not-allowed border border-slate-200">
                    Tükendi
                </button>
            @elseif($hasVariants)
                <button type="button" 
                    class="by-btn-primary w-full py-2 text-xs font-bold open-variant-modal-trigger"
                    data-product-id="{{ $product->id }}"
                    data-product-name="{{ $product->localizedName() }}"
                    data-base-price="{{ $product->price }}"
                    data-action-url="{{ route('cart.add', $product) }}"
                    data-variants="{{ json_encode($product->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => number_format((float)($product->price + $v->price_adjustment), 2, ',', '.'), 'stock' => $v->stock])) }}">
                    Seçenekler & Sepete Ekle
                </button>
            @else
                <button type="button" 
                    class="by-btn-primary w-full py-2 text-xs font-bold ajax-add-to-cart-trigger"
                    data-product-id="{{ $product->id }}"
                    data-url="{{ route('cart.add', $product) }}">
                    Sepete Ekle
                </button>
            @endif
        </div>
    </div>
</div>
