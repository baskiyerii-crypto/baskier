@extends('layouts.account')

@section('title', 'Siparişlerim - BaskıYeri')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;
@endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Siparişlerim</h1>
            <p class="text-xs text-muted mt-0.5">Tüm siparişlerinizin üretim, kargo ve teslimat durumu</p>
        </div>
        <div>
            <a href="{{ route('products.index') }}" class="btn btn-cta text-xs">
                + Alışverişe Başla
            </a>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    @if($orders->isEmpty())
        <div class="by-card p-12 text-center bg-surface border border-border">
            <div class="w-12 h-12 mx-auto rounded-full bg-canvas flex items-center justify-center text-muted mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <h3 class="text-base font-bold text-ink">Henüz verilmiş bir siparişiniz bulunmuyor</h3>
            <p class="text-sm text-muted mt-1 max-w-md mx-auto">Kartvizit, broşür, tabela veya özel üretim ihtiyaçlarınız için hemen sipariş verebilirsiniz.</p>
            <div class="mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-cta text-xs">Ürünleri Keşfet</a>
            </div>
        </div>
    @else
        <div class="by-card bg-surface overflow-hidden border border-border">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                            <th class="px-5 py-3.5">Sipariş No</th>
                            <th class="px-5 py-3.5">Tarih</th>
                            <th class="px-5 py-3.5">Tür</th>
                            <th class="px-5 py-3.5">Satıcı</th>
                            <th class="px-5 py-3.5">Tutar</th>
                            <th class="px-5 py-3.5">Durum</th>
                            <th class="px-5 py-3.5 text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($orders as $o)
                            @php
                                $typeLabel = match($o->type) {
                                    'product' => 'Pazaryeri',
                                    'quote' => 'Özel Teklif',
                                    'freelancer' => 'Freelancer',
                                    default => ucfirst($o->type ?? 'Sipariş'),
                                };
                                $statusVariant = match($o->status) {
                                    OrderStatus::CONFIRMED, OrderStatus::PENDING, 'paid' => 'info',
                                    OrderStatus::DESIGN_REVIEW, OrderStatus::IN_PRODUCTION => 'warning',
                                    OrderStatus::SHIPPED => 'info',
                                    OrderStatus::DELIVERED, OrderStatus::COMPLETED => 'success',
                                    OrderStatus::CANCELLED => 'danger',
                                    default => 'neutral',
                                };
                            @endphp
                            <tr class="hover:bg-canvas/40 transition-colors">
                                <td class="px-5 py-4 font-mono font-bold text-ink text-xs">
                                    #{{ $o->order_number }}
                                </td>
                                <td class="px-5 py-4 text-xs text-muted">
                                    {{ $o->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-canvas text-muted border border-border">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-xs font-medium text-ink">
                                    {{ $o->vendor?->name ?? 'BaskıYeri Platformu' }}
                                </td>
                                <td class="px-5 py-4 text-xs font-extrabold text-ink">
                                    ₺{{ number_format($o->subtotal, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge :variant="$statusVariant">{{ UiLabels::orderStatus($o->status) }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('account.orders.show', $o) }}" class="btn btn-secondary text-xs py-1.5 px-3">
                                        Detay →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-5">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
