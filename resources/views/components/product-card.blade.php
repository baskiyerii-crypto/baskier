@props(['product'])

@php
    $isQuote = $product->isQuoteBased();
    $hasVariants = $product->hasVariants();
    $isOutOfStock = ! $isQuote && ((int) $product->stock <= 0);
    $formattedPrice = number_format((float) $product->price, 2, ',', '.');
    $categoryName = $product->category?->localizedName() ?? __('ui.category');
    $vendorName = $product->vendor?->name ?? __('home.vendor_fallback');
@endphp

<div class="group flex flex-col justify-between rounded-xl border border-[#DEDAD2] bg-white overflow-hidden shadow-xs hover:-translate-y-0.5 hover:shadow-md hover:border-[#C2410C]/40 transition duration-150 ease-out product-card" data-product-id="{{ $product->id }}">
    <div>
        {{-- Product Image (Links to Detail) with Branded Local Fallback --}}
        <a href="{{ route('products.show', $product->slug) }}" class="relative block aspect-4/3 bg-[#F7F5F0] overflow-hidden" title="{{ $product->localizedName() }}">
            @if($product->main_image)
                <img 
                    src="{{ asset('storage/'.$product->main_image) }}" 
                    alt="{{ $product->localizedName() }}" 
                    width="400"
                    height="300"
                    loading="lazy" 
                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                />
            @else
                <img 
                    src="{{ asset('images/placeholder-product.svg') }}" 
                    alt="{{ $product->localizedName() }}" 
                    width="400"
                    height="300"
                    loading="lazy" 
                    class="h-full w-full object-contain p-3 transition duration-300 group-hover:scale-105"
                />
            @endif

            {{-- Status Badges --}}
            <div class="absolute top-2.5 left-2.5 flex flex-col gap-1">
                @if($isOutOfStock)
                    <span class="inline-block rounded-md bg-[#182023]/85 px-2 py-0.5 text-[11px] font-bold text-white backdrop-blur-xs">
                        {{ __('ui.out_of_stock') ?? 'Tükendi' }}
                    </span>
                @elseif($isQuote)
                    <span class="inline-block rounded-md bg-[#182023]/85 px-2 py-0.5 text-[11px] font-bold text-orange-300 backdrop-blur-xs">
                        Teklifli Ürün
                    </span>
                @elseif($product->isDigital())
                    <span class="inline-block rounded-md bg-emerald-700/90 px-2 py-0.5 text-[11px] font-bold text-white backdrop-blur-xs">
                        Dijital
                    </span>
                @endif
            </div>
        </a>

        {{-- Meta, Vendor with Trust Badge, and Title --}}
        <div class="p-3.5 sm:p-4 pb-2">
            <div class="flex items-center justify-between text-[11px] text-[#596166] mb-1 gap-2">
                <span class="font-semibold uppercase tracking-wider truncate max-w-[50%]">{{ $categoryName }}</span>
                <span class="truncate max-w-[50%] flex items-center gap-1">
                    <span class="truncate text-[#596166]">{{ $vendorName }}</span>
                    @if($product->vendor)
                        <x-trust-badge :vendor="$product->vendor" size="sm" :showLabel="false" />
                    @endif
                </span>
            </div>

            <a href="{{ route('products.show', $product->slug) }}" class="block font-bold text-sm text-[#182023] line-clamp-2 hover:text-[#C2410C] transition leading-snug" title="{{ $product->localizedName() }}">
                {{ $product->localizedName() }}
            </a>
        </div>
    </div>

    {{-- Price & Single CTA Container --}}
    <div class="p-3.5 sm:p-4 pt-2 mt-auto">
        <div class="mb-3 flex items-baseline justify-between">
            @if($isQuote)
                <span class="text-xs sm:text-sm font-bold text-[#182023]">Teklif Usulü</span>
                @if($product->price_min && $product->price_max)
                    <span class="text-xs text-[#596166]">₺{{ number_format((float)$product->price_min, 0, ',', '.') }} - ₺{{ number_format((float)$product->price_max, 0, ',', '.') }}</span>
                @endif
            @else
                <div class="flex items-baseline gap-1">
                    <span class="text-base font-extrabold text-[#182023]">₺{{ $formattedPrice }}</span>
                    <span class="text-[10px] text-[#596166] font-medium">+KDV</span>
                </div>
                @if($hasVariants)
                    <span class="text-[10px] font-semibold text-[#596166] bg-[#F7F5F0] px-1.5 py-0.5 rounded-sm border border-[#DEDAD2]">Varyantlı</span>
                @endif
            @endif
        </div>

        {{-- Action Buttons --}}
        <div>
            @if($isQuote)
                <a href="{{ route('quote-requests.create', ['product_id' => $product->id, 'type' => 'physical_quote']) }}" class="inline-flex min-h-[42px] w-full items-center justify-center rounded-lg border border-[#DEDAD2] bg-white text-xs font-bold text-[#182023] hover:bg-[#F7F5F0] hover:border-[#C2410C] hover:text-[#C2410C] transition shadow-xs text-center">
                    Hizmet Teklifi Al
                </a>
            @elseif($isOutOfStock)
                <button type="button" disabled class="inline-flex min-h-[42px] w-full items-center justify-center rounded-lg bg-[#F7F5F0] text-xs font-semibold text-[#596166]/60 cursor-not-allowed border border-[#DEDAD2]">
                    Tükendi
                </button>
            @elseif($hasVariants)
                <button type="button" 
                    class="inline-flex min-h-[42px] w-full items-center justify-center rounded-lg bg-[#C2410C] px-3 text-xs font-bold text-white shadow-xs hover:bg-[#9A3412] transition open-variant-modal-trigger"
                    data-product-id="{{ $product->id }}"
                    data-product-name="{{ $product->localizedName() }}"
                    data-base-price="{{ $product->price }}"
                    data-action-url="{{ route('cart.add', $product) }}"
                    data-variants="{{ json_encode($product->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => number_format((float)($product->price + $v->price_adjustment), 2, ',', '.'), 'stock' => $v->stock])) }}">
                    Seçenekler & Sepete Ekle
                </button>
            @else
                <button type="button" 
                    class="inline-flex min-h-[42px] w-full items-center justify-center rounded-lg bg-[#C2410C] px-3 text-xs font-bold text-white shadow-xs hover:bg-[#9A3412] transition ajax-add-to-cart-trigger"
                    data-product-id="{{ $product->id }}"
                    data-url="{{ route('cart.add', $product) }}">
                    Sepete Ekle
                </button>
            @endif
        </div>
    </div>
</div>
