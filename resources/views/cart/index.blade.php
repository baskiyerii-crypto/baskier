@extends('layouts.app')

@section('title', 'Sepetim – BaskıYeri')
@section('meta_description', 'BaskıYeri sepetinizdeki ürünleri görüntüleyin, adetleri güncelleyin ve güvenle ödemeye geçin.')

@section('content')
<div class="by-container py-6 sm:py-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-4 border-b border-[#DEDAD2]">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-[#596166] mb-0.5">Alışveriş / Sepet</p>
            <h1 class="text-2xl font-bold tracking-tight text-[#182023]">
                Sepetim <span class="text-sm font-normal text-[#596166]">({{ $items->sum('quantity') }} Adet)</span>
            </h1>
        </div>
        <x-button href="{{ route('products.index') }}" variant="secondary" size="sm">
            Alışverişe Devam Et →
        </x-button>
    </div>

    @if($items->isEmpty())
        <x-empty-state 
            title="Sepetiniz henüz boş"
            message="Baskı, reklam ve promosyon ürünlerini keşfedin; ihtiyacınıza uygun ürünleri sepetinize ekleyin."
            actionText="Ürünleri Keşfet"
            actionUrl="{{ route('products.index') }}"
        />
    @else
        <div class="cart-layout">
            {{-- Products Table Card --}}
            <div class="rounded-xl border border-[#DEDAD2] bg-white shadow-xs overflow-hidden">
                <table class="table cart-table mb-0">
                    <caption class="sr-only">Sepetinizdeki ürünler, adetleri ve fiyatları</caption>
                    <thead>
                        <tr>
                            <th scope="col">Ürün</th>
                            <th scope="col">Birim Fiyat</th>
                            <th scope="col">Adet</th>
                            <th scope="col">Ara Toplam</th>
                            <th scope="col"><span class="sr-only">İşlem</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @php
                                $unit = (float) $item->product->price + (float) ($item->variant?->price_adjustment ?? 0);
                                $stock = min(999, (int) ($item->variant?->stock ?? $item->product->stock));
                            @endphp
                            <tr>
                                <td class="cart-product">
                                    <a href="{{ route('products.show', $item->product->slug) }}" class="font-bold text-[#182023] hover:text-[#C2410C] transition">
                                        {{ $item->product->name }}
                                    </a>
                                    @if($item->variant)
                                        <div class="text-xs text-[#596166] mt-0.5">Seçenek: {{ $item->variant->name }}</div>
                                    @endif
                                    <div class="text-xs text-[#596166] mt-0.5">Satıcı: {{ $item->product->vendor?->name }}</div>
                                    @if(!$item->product->is_active || $stock < $item->quantity)
                                        <p class="text-xs font-semibold text-rose-600 mt-2">
                                            {{ !$item->product->is_active ? 'Bu ürün artık satışta değil. Sepetten çıkarın.' : 'Stok değişti. Adedi güncelleyin veya ürünü çıkarın.' }}
                                        </p>
                                    @endif
                                </td>
                                <td data-label="Birim fiyat" class="cart-money text-sm text-[#182023]">
                                    ₺{{ number_format($unit, 2, ',', '.') }}
                                </td>
                                <td data-label="Adet">
                                    <form action="{{ route('cart.update', $item) }}" method="post" class="cart-quantity">
                                        @csrf 
                                        @method('PUT')
                                        <label for="quantity-{{ $item->id }}" class="sr-only">{{ $item->product->name }} adedi</label>
                                        <input 
                                            id="quantity-{{ $item->id }}" 
                                            type="number" 
                                            name="quantity" 
                                            value="{{ $item->quantity }}" 
                                            min="1" 
                                            max="{{ max(1, $stock) }}" 
                                            step="1" 
                                            inputmode="numeric" 
                                            required 
                                            class="w-20 min-h-[38px] rounded-lg border border-[#DEDAD2] bg-white px-2 py-1 text-center text-sm font-bold text-[#182023] outline-none focus:border-[#C2410C]"
                                        />
                                        <button type="submit" class="inline-flex min-h-[38px] items-center justify-center rounded-lg border border-[#DEDAD2] bg-white px-3 text-xs font-semibold text-[#182023] hover:bg-[#F7F5F0] transition" aria-label="{{ $item->product->name }} adedini güncelle">
                                            Güncelle
                                        </button>
                                    </form>
                                </td>
                                <td data-label="Ara toplam" class="cart-money font-bold text-sm text-[#182023]">
                                    ₺{{ number_format($item->lineTotal(), 2, ',', '.') }}
                                </td>
                                <td class="cart-remove">
                                    <form action="{{ route('cart.remove', $item) }}" method="post">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800 underline p-2" aria-label="{{ $item->product->name }} ürününü sepetten çıkar">
                                            Kaldır
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary Card --}}
            <aside class="rounded-xl border border-[#DEDAD2] bg-white p-6 shadow-xs cart-summary" aria-label="Sipariş özeti">
                <h2 class="text-base font-bold text-[#182023] mb-4 pb-2 border-b border-[#DEDAD2]">Sipariş Özeti</h2>
                <div class="flex justify-between gap-3 text-xs text-[#596166] mb-3">
                    <span>Çeşit Sayısı</span>
                    <span class="font-semibold text-[#182023]">{{ $items->count() }} çeşit</span>
                </div>
                <div class="flex justify-between gap-3 text-xs text-[#596166] mb-4">
                    <span>Toplam Adet</span>
                    <span class="font-semibold text-[#182023]">{{ $items->sum('quantity') }} adet</span>
                </div>
                <div class="flex justify-between gap-3 border-t border-[#DEDAD2] pt-4 mb-6">
                    <span class="font-bold text-sm text-[#182023]">Ara Toplam</span>
                    <strong class="text-xl font-extrabold text-[#C2410C] cart-money">₺{{ number_format($total, 2, ',', '.') }}</strong>
                </div>
                <x-button href="{{ route('checkout.index') }}" variant="primary" fullWidth size="lg">
                    Ödemeye Geç →
                </x-button>
                <div class="mt-4 pt-3 border-t border-[#DEDAD2]/60 space-y-1.5 text-[11px] text-[#596166]">
                    <p class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Güvenli 256-bit SSL ödeme altyapısı</span>
                    </p>
                    <p class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Sipariş ve teslimat adresi sonraki adımda seçilir</span>
                    </p>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection
