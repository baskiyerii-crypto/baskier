@extends('layouts.vendor')

@section('title', 'Siparişler - Satıcı Paneli')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;
    $currentStatus = request('status', '');
@endphp

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Sipariş Yönetimi</h1>
        <p class="text-xs text-muted mt-0.5">Tüm müşteri siparişlerinizi, baskı provalarını ve kargo süreçlerini buradan yönetin.</p>
    </div>
    <form method="GET" action="{{ route('vendor.orders.index') }}" class="flex gap-2">
        @if($currentStatus !== '')
            <input type="hidden" name="status" value="{{ $currentStatus }}">
        @endif
        <input type="text" name="q" value="{{ request('q') }}" class="form-control text-xs min-w-[200px]" placeholder="Sipariş no veya alıcı ara...">
        <button type="submit" class="btn btn-secondary text-xs">Ara</button>
        @if(request()->hasAny(['q', 'status']))
            <a href="{{ route('vendor.orders.index') }}" class="btn btn-secondary text-xs">Temizle</a>
        @endif
    </form>
</div>

<!-- Durum Sekmeleri -->
<div class="flex flex-wrap gap-1.5 mb-6 p-1.5 rounded-xl border border-border bg-surface">
    <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $currentStatus === '' ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('vendor.orders.index', array_filter(['q' => request('q')])) }}">Tümü</a>
    <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $currentStatus === OrderStatus::CONFIRMED ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::CONFIRMED, 'q' => request('q')])) }}">Yeni Onaylanan</a>
    <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $currentStatus === OrderStatus::DESIGN_REVIEW ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::DESIGN_REVIEW, 'q' => request('q')])) }}">Prova Bekleyen</a>
    <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $currentStatus === OrderStatus::IN_PRODUCTION ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::IN_PRODUCTION, 'q' => request('q')])) }}">Üretimde</a>
    <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $currentStatus === OrderStatus::SHIPPED ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::SHIPPED, 'q' => request('q')])) }}">Kargoda</a>
    <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ in_array($currentStatus, [OrderStatus::DELIVERED, OrderStatus::COMPLETED]) ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::DELIVERED, 'q' => request('q')])) }}">Tamamlanan</a>
</div>

<div class="by-card bg-surface border border-border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                    <th class="px-5 py-3">Sipariş No</th>
                    <th class="px-5 py-3">Tarih</th>
                    <th class="px-5 py-3">Alıcı / Müşteri</th>
                    <th class="px-5 py-3">Sipariş Kalemleri</th>
                    <th class="px-5 py-3">Net Kazanç</th>
                    <th class="px-5 py-3">Durum</th>
                    <th class="px-5 py-3 text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($orders as $order)
                    @php
                        $badgeVariant = match($order->status) {
                            OrderStatus::CONFIRMED, OrderStatus::PENDING => 'info',
                            OrderStatus::DESIGN_REVIEW, OrderStatus::IN_PRODUCTION => 'warning',
                            OrderStatus::READY_TO_SHIP => 'neutral',
                            OrderStatus::SHIPPED => 'info',
                            OrderStatus::DELIVERED, OrderStatus::COMPLETED => 'success',
                            OrderStatus::CANCELLED => 'danger',
                            default => 'neutral',
                        };
                    @endphp
                    <tr class="hover:bg-canvas/30 transition-colors">
                        <td class="px-5 py-3.5 font-mono font-bold text-xs text-ink">#{{ $order->order_number }}</td>
                        <td class="px-5 py-3.5 text-xs text-muted">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        <td class="px-5 py-3.5 text-xs font-medium text-ink">{{ $order->user?->name ?? 'Misafir Alıcı' }}</td>
                        <td class="px-5 py-3.5 text-xs text-muted">
                            @if($order->items && $order->items->isNotEmpty())
                                {{ Str::limit($order->items->pluck('name')->join(', '), 35) }}
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] bg-canvas text-muted border border-border ml-1">({{ $order->items->sum('quantity') }} parça)</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs font-bold text-ink">₺{{ number_format($order->vendor_amount ?? $order->subtotal, 2, ',', '.') }}</td>
                        <td class="px-5 py-3.5">
                            <x-badge :variant="$badgeVariant">
                                {{ UiLabels::orderStatus($order->status) }}
                            </x-badge>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('vendor.orders.show', $order) }}" class="btn btn-secondary text-xs py-1.5 px-3">
                                İncele &amp; Yönet →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted text-xs py-8">
                            Bu filtreye uygun sipariş bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
