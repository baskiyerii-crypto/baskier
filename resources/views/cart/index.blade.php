@extends('layouts.app')
@section('title', 'Sepetim - BaskıYeri')
@section('content')
<div class="by-container py-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div><p class="text-sm text-slate-500 mb-1">Alışveriş / Sepet</p><h1 class="text-2xl font-bold">Sepetim <span class="text-base font-normal text-slate-500">({{ $items->sum('quantity') }} adet)</span></h1></div>
        <a href="{{ route('products.index') }}" class="by-btn-secondary">Alışverişe devam et</a>
    </div>
    @if($items->isEmpty())
        <div class="by-card p-8 text-center">
            <h2 class="text-xl font-semibold mb-2">Sepetiniz henüz boş</h2>
            <p class="text-slate-600 mb-6">Baskı, reklam ve promosyon ürünlerini keşfedin; ihtiyacınıza uygun ürünleri sepetinize ekleyin.</p>
            <a href="{{ route('products.index') }}" class="by-btn-cta">Ürünleri keşfet</a>
        </div>
    @else
        <div class="cart-layout">
            <div class="by-card overflow-hidden">
                <table class="table cart-table mb-0">
                    <caption class="sr-only">Sepetinizdeki ürünler, adetleri ve fiyatları</caption>
                    <thead><tr><th scope="col">Ürün</th><th scope="col">Birim fiyat</th><th scope="col">Adet</th><th scope="col">Ara toplam</th><th scope="col"><span class="sr-only">İşlem</span></th></tr></thead>
                    <tbody>
                        @foreach($items as $item)
                            @php
                                $unit = (float) $item->product->price + (float) ($item->variant?->price_adjustment ?? 0);
                                $stock = min(999, (int) ($item->variant?->stock ?? $item->product->stock));
                            @endphp
                            <tr>
                                <td class="cart-product">
                                    <a href="{{ route('products.show', $item->product->slug) }}" class="font-semibold text-slate-900">{{ $item->product->name }}</a>
                                    @if($item->variant)<div class="small text-muted mt-1">{{ $item->variant->name }}</div>@endif
                                    <div class="small text-muted mt-1">{{ $item->product->vendor?->name }}</div>
                                    @if(!$item->product->is_active || $stock < $item->quantity)
                                        <p class="text-sm text-rose-700 mt-2">{{ !$item->product->is_active ? 'Bu ürün artık satışta değil. Sepetten çıkarın.' : 'Stok değişti. Adedi güncelleyin veya ürünü çıkarın.' }}</p>
                                    @endif
                                </td>
                                <td data-label="Birim fiyat" class="cart-money">₺{{ number_format($unit, 2, ',', '.') }}</td>
                                <td data-label="Adet">
                                    <form action="{{ route('cart.update', $item) }}" method="post" class="cart-quantity">
                                        @csrf @method('PUT')
                                        <label for="quantity-{{ $item->id }}" class="sr-only">{{ $item->product->name }} adedi</label>
                                        <input id="quantity-{{ $item->id }}" type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ max(1, $stock) }}" step="1" inputmode="numeric" required class="form-control">
                                        <button type="submit" class="by-btn-secondary" aria-label="{{ $item->product->name }} adedini güncelle">Güncelle</button>
                                    </form>
                                </td>
                                <td data-label="Ara toplam" class="cart-money font-semibold">₺{{ number_format($item->lineTotal(), 2, ',', '.') }}</td>
                                <td class="cart-remove">
                                    <form action="{{ route('cart.remove', $item) }}" method="post">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-rose-700 underline p-2" aria-label="{{ $item->product->name }} ürününü sepetten çıkar">Kaldır</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <aside class="by-card p-6 cart-summary" aria-label="Sipariş özeti">
                <h2 class="text-lg font-bold mb-5">Sipariş özeti</h2>
                <div class="flex justify-between gap-3 text-sm text-slate-600 mb-4"><span>{{ $items->count() }} ürün çeşidi</span><span>{{ $items->sum('quantity') }} adet</span></div>
                <div class="flex justify-between gap-3 border-t border-slate-200 pt-4"><span class="font-semibold">Ürünler toplamı</span><strong class="text-xl cart-money">₺{{ number_format($total, 2, ',', '.') }}</strong></div>
                <a href="{{ route('checkout.index') }}" class="by-btn-cta w-full mt-6">Ödemeye geç →</a>
                <p class="text-sm text-slate-500 mt-3">Teslimat adresinizi ve ödeme yönteminizi sonraki adımda seçebilirsiniz.</p>
            </aside>
        </div>
    @endif
</div>
@endsection
