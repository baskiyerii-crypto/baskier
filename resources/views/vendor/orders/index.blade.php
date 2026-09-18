@extends('layouts.vendor')

@section('title', 'Siparişler')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;
    $currentStatus = request('status', '');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h5 fw-bold mb-1">Sipariş Yönetimi</h1>
        <p class="text-muted small mb-0">Tüm müşteri siparişlerinizi, baskı provalarını ve kargo süreçlerini buradan yönetin.</p>
    </div>
    <form method="GET" action="{{ route('vendor.orders.index') }}" class="d-flex gap-2">
        @if($currentStatus !== '')
            <input type="hidden" name="status" value="{{ $currentStatus }}">
        @endif
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="min-width:220px;" placeholder="Sipariş no veya alıcı ara...">
        <button type="submit" class="btn btn-secondary btn-sm">Ara</button>
        @if(request()->hasAny(['q', 'status']))
            <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary btn-sm">Filtreyi Temizle</a>
        @endif
    </form>
</div>

<!-- Durum Sekmeleri -->
<ul class="nav nav-pills mb-3 gap-1 bg-white p-2 rounded border small">
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === '' ? 'active' : '' }}" href="{{ route('vendor.orders.index', array_filter(['q' => request('q')])) }}">Tümü</a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === OrderStatus::PENDING_PAYMENT ? 'active' : '' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::PENDING_PAYMENT, 'q' => request('q')])) }}">
            Ödeme Bekleyen
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === OrderStatus::CONFIRMED ? 'active' : '' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::CONFIRMED, 'q' => request('q')])) }}">Yeni Onaylanan</a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === OrderStatus::DESIGN_REVIEW ? 'active' : '' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::DESIGN_REVIEW, 'q' => request('q')])) }}">Prova Bekleyen</a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === OrderStatus::IN_PRODUCTION ? 'active' : '' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::IN_PRODUCTION, 'q' => request('q')])) }}">Üretimde</a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === OrderStatus::SHIPPED ? 'active' : '' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::SHIPPED, 'q' => request('q')])) }}">Kargoda</a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ in_array($currentStatus, [OrderStatus::DELIVERED, OrderStatus::COMPLETED]) ? 'active' : '' }}" href="{{ route('vendor.orders.index', array_filter(['status' => OrderStatus::DELIVERED, 'q' => request('q')])) }}">Tamamlanan</a>
    </li>
</ul>

<div class="card overflow-hidden shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Sipariş No</th>
                    <th>Tarih</th>
                    <th>Alıcı / Müşteri</th>
                    <th>Sipariş Kalemleri</th>
                    <th>Net Kazanç</th>
                    <th>Durum</th>
                    <th class="text-end">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="fw-bold">{{ $order->order_number }}</td>
                        <td class="small text-muted">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $order->user?->name ?? 'Misafir Alıcı' }}</td>
                        <td class="small text-muted">
                            @if($order->items && $order->items->isNotEmpty())
                                {{ Str::limit($order->items->pluck('name')->join(', '), 40) }}
                                <span class="badge bg-light text-dark border">({{ $order->items->sum('quantity') }} parça)</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-bold text-success">₺{{ number_format($order->vendor_amount ?? $order->subtotal, 2, ',', '.') }}</td>
                        <td>
                            <span class="badge rounded-pill
                                @if($order->status === OrderStatus::PENDING_PAYMENT) bg-warning text-dark border border-warning
                                @elseif(in_array($order->status, [OrderStatus::CONFIRMED, OrderStatus::PENDING])) bg-primary
                                @elseif($order->status === OrderStatus::DESIGN_REVIEW) bg-warning text-dark
                                @elseif($order->status === OrderStatus::IN_PRODUCTION) bg-info text-dark
                                @elseif($order->status === OrderStatus::READY_TO_SHIP) bg-secondary
                                @elseif($order->status === OrderStatus::SHIPPED) bg-primary-subtle text-primary border border-primary
                                @elseif($order->status === OrderStatus::DELIVERED || $order->status === OrderStatus::COMPLETED) bg-success
                                @elseif($order->status === OrderStatus::CANCELLED) bg-danger
                                @else bg-secondary @endif">
                                {{ UiLabels::orderStatus($order->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center gap-1">
                                @if($order->status === OrderStatus::PENDING_PAYMENT)
                                    <form method="POST" action="{{ route('vendor.orders.confirm-payment', $order) }}" onsubmit="return confirm('Müşterinin havale/EFT ödemesi banka hesabınıza geçtiyse onaylamak istediğinize emin misiniz?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm fw-semibold" title="Havale Ödemesini Onayla">
                                            ✓ Ödemeyi Onayla
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('vendor.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">İncele & Yönet →</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            Bu filtreye uygun sipariş bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
