@extends('layouts.vendor')

@section('title', 'Özet & Dashboard')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;
@endphp

<!-- KPI Kartları -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #ecfdf5, #ffffff);">
            <div class="small text-muted mb-1">Toplam Hakediş (Ciro)</div>
            <div class="h4 mb-0 fw-bold text-success">₺{{ number_format($totalRevenue, 2, ',', '.') }}</div>
            <a href="{{ route('vendor.payout-requests.index') }}" class="small text-success text-decoration-none mt-2 d-inline-block fw-medium">Para Çekme →</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #eff6ff, #ffffff);">
            <div class="small text-muted mb-1">Aktif Siparişler</div>
            <div class="h4 mb-0 fw-bold text-primary">{{ $ordersPending }}</div>
            <a href="{{ route('vendor.orders.index') }}" class="small text-primary text-decoration-none mt-2 d-inline-block fw-medium">Tümünü Yönet →</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #fffbeb, #ffffff);">
            <div class="small text-muted mb-1">Baskı Provası Bekleyen</div>
            <div class="h4 mb-0 fw-bold text-warning">{{ $proofPendingCount }}</div>
            <a href="{{ route('vendor.orders.index', ['status' => OrderStatus::DESIGN_REVIEW]) }}" class="small text-warning text-decoration-none mt-2 d-inline-block fw-medium">Provaları Yükle →</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #faf5ff, #ffffff);">
            <div class="small text-muted mb-1">Kargolanacak / Üretimde</div>
            <div class="h4 mb-0 fw-bold text-purple" style="color:#7c3aed;">{{ $readyToShipCount }}</div>
            <a href="{{ route('vendor.orders.index', ['status' => OrderStatus::IN_PRODUCTION]) }}" class="small text-purple text-decoration-none mt-2 d-inline-block fw-medium" style="color:#7c3aed;">Kargo Bekleyenler →</a>
        </div>
    </div>
</div>

<!-- Hızlı Eylemler ve Mağaza Durumu -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card p-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="h6 fw-bold mb-0">{{ $vendor->name }}</h2>
                    @if(!$vendor->is_active)
                        <span class="badge bg-warning text-dark">Yönetici Onayı Bekliyor</span>
                    @else
                        <span class="badge bg-success-subtle text-success">Aktif Mağaza</span>
                    @endif
                </div>
                <a href="{{ route('vendors.show', $vendor->slug) }}" target="_blank" class="small text-decoration-none">Mağaza Sayfasını Gör ↗</a>
            </div>
            <p class="small text-muted mb-2">
                {{ $vendor->description ? Str::limit($vendor->description, 150) : 'Mağaza açıklaması girilmemiş.' }}
            </p>
            @if($vendor->businessTypes->isNotEmpty())
                <div class="mt-2">
                    @foreach($vendor->businessTypes as $bt)
                        <span class="badge bg-light text-dark border me-1">{{ $bt->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-4 d-grid gap-2">
        <a href="{{ route('vendor.products.create') }}" class="btn btn-success py-3 fw-bold">+ Yeni Ürün Ekle</a>
        <a href="{{ route('vendor.quote-requests.index') }}" class="btn btn-outline-primary btn-sm py-2">
            Açık Teklif Talepleri ({{ $openQuoteRequestsCount }})
        </a>
    </div>
</div>

<!-- Son Gelen Siparişler -->
<div class="card p-4 mb-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 fw-bold mb-0">Son Gelen Siparişler</h2>
        <a href="{{ route('vendor.orders.index') }}" class="small fw-semibold text-decoration-none">Tüm Siparişleri Gör →</a>
    </div>
    @if($recentOrders->isEmpty())
        <div class="text-center py-4 text-muted small">
            Henüz siparişiniz bulunmuyor. Ürünlerinizi güncel tutarak satışlarınızı artırabilirsiniz.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Sipariş No</th>
                        <th>Tarih</th>
                        <th>Alıcı</th>
                        <th>Kalemler</th>
                        <th>Net Tutar</th>
                        <th>Durum</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td class="fw-bold">{{ $order->order_number }}</td>
                            <td class="text-muted">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $order->user?->name ?? 'Misafir Alıcı' }}</td>
                            <td class="text-muted">{{ Str::limit($order->items->pluck('name')->join(', '), 30) ?: 'Kalem detayı yok' }}</td>
                            <td class="fw-bold text-success">₺{{ number_format($order->vendor_amount ?? $order->subtotal, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge rounded-pill
                                    @if(in_array($order->status, [OrderStatus::CONFIRMED, OrderStatus::PENDING])) bg-primary
                                    @elseif($order->status === OrderStatus::DESIGN_REVIEW) bg-warning text-dark
                                    @elseif($order->status === OrderStatus::IN_PRODUCTION) bg-info text-dark
                                    @elseif($order->status === OrderStatus::SHIPPED) bg-primary-subtle text-primary border border-primary
                                    @elseif($order->status === OrderStatus::DELIVERED) bg-success
                                    @else bg-secondary @endif">
                                    {{ UiLabels::orderStatus($order->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('vendor.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">Yönet →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Modül ve Abonelik Durumları -->
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 fw-bold mb-0">Modül Yetkileri ve Abonelikler</h2>
        <a href="{{ route('vendor.subscriptions.index') }}" class="small fw-semibold text-decoration-none">Yönet →</a>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted mb-1">Fiziksel Ürün Satışı</div>
                <span class="badge bg-success-subtle text-success">Aktif</span>
                <div class="small text-muted mt-2">Aylık ücret yok. Satış başına standart komisyon uygulanır.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted mb-1">Freelancer (Dijital Ürün) Modülü</div>
                <span class="badge {{ $vendor->hasActiveFreelancerModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveFreelancerModule() ? 'Aktif' : 'Pasif' }}
                </span>
                <div class="small text-muted mt-2">Bitiş: {{ optional($vendor->freelancer_expires_at)->format('d.m.Y H:i') ?? '-' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted mb-1">Teklif Verme Modülü</div>
                <span class="badge {{ $vendor->hasActiveQuotesModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveQuotesModule() ? 'Aktif' : 'Pasif' }}
                </span>
                <div class="small text-muted mt-2">Bitiş: {{ optional($vendor->quotes_expires_at)->format('d.m.Y H:i') ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
