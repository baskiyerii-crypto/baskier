@extends('layouts.vendor')

@section('title', 'Genel bakış')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;
@endphp

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #ecfdf5, #ffffff);">
            <div class="small text-muted mb-1">Toplam Hakediş (Ciro)</div>
            <div class="h4 mb-0 fw-bold text-success metric-count" data-count="{{ (int) $totalRevenue }}">₺{{ number_format($totalRevenue, 2, ',', '.') }}</div>
            <a href="{{ route('vendor.payout-requests.index') }}" class="small text-success text-decoration-none mt-2 d-inline-block fw-medium">Para Çekme →</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #eff6ff, #ffffff);">
            <div class="small text-muted mb-1">Aktif Siparişler</div>
            <div class="h4 mb-0 fw-bold text-primary metric-count" data-count="{{ $ordersPending }}">{{ $ordersPending }}</div>
            <a href="{{ route('vendor.orders.index') }}" class="small text-primary text-decoration-none mt-2 d-inline-block fw-medium">Tümünü Yönet →</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #fffbeb, #ffffff);">
            <div class="small text-muted mb-1">Baskı Provası Bekleyen</div>
            <div class="h4 mb-0 fw-bold text-warning metric-count" data-count="{{ $proofPendingCount }}">{{ $proofPendingCount }}</div>
            <a href="{{ route('vendor.orders.index', ['status' => OrderStatus::DESIGN_REVIEW]) }}" class="small text-warning text-decoration-none mt-2 d-inline-block fw-medium">Provaları Yükle →</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #faf5ff, #ffffff);">
            <div class="small text-muted mb-1">Yaklaşan Hakediş</div>
            <div class="h4 mb-0 fw-bold" style="color:#7c3aed;">₺{{ number_format($upcomingPayouts ?? 0, 2, ',', '.') }}</div>
            <div class="small text-muted mt-2">Kargolanacak: {{ $readyToShipCount }}</div>
        </div>
    </div>
</div>

@if(($moduleEnds ?? collect())->isNotEmpty())
<div class="alert alert-warning small mb-4">
    Yaklaşan abonelik bitişleri:
    @foreach($moduleEnds as $mod => $date)
        <strong>{{ $mod }}</strong> {{ $date->format('d.m.Y') }}{{ !$loop->last ? ',' : '' }}
    @endforeach
</div>
@endif

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
            <div class="mt-3 small">Bakiye: <strong>₺{{ number_format($vendor->balance, 2, ',', '.') }}</strong></div>
        </div>
    </div>
    <div class="col-md-4 d-grid gap-2">
        <a href="{{ route('vendor.products.create') }}" class="btn btn-success py-3 fw-bold">+ Yeni Ürün Ekle</a>
        @if($vendor->hasActiveQuotesModule())
            <a href="{{ route('vendor.quote-requests.index') }}" class="btn btn-outline-primary btn-sm py-2">
                Açık Teklif Talepleri ({{ $openQuoteRequestsCount }})
            </a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card p-3 shadow-sm border-0 h-100">
            <div class="small text-muted mb-2">{{ __('panel.revenue_30d') }}</div>
            <canvas id="vendorRevenueChart" height="120"></canvas>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card p-3 shadow-sm border-0 h-100">
            <div class="small text-muted mb-2">{{ __('panel.order_status_chart') }}</div>
            <canvas id="vendorStatusChart" height="120"></canvas>
        </div>
    </div>
</div>

<div class="card p-4 mb-4 shadow-sm">
        <a href="{{ route('vendor.orders.index') }}" class="small fw-semibold text-decoration-none">Tümü →</a>
    </div>
    @if($recentOrders->isEmpty())
        <p class="text-muted small mb-0">Henüz sipariş yok.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>No</th><th>Müşteri</th><th>Tutar</th><th>Durum</th></tr></thead>
                <tbody>
                @foreach($recentOrders as $o)
                    <tr>
                        <td><a href="{{ route('vendor.orders.show', $o) }}">#{{ $o->order_number }}</a></td>
                        <td>{{ $o->user?->name }}</td>
                        <td>₺{{ number_format($o->vendor_amount ?? $o->subtotal, 2, ',', '.') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ class_exists(UiLabels::class) ? UiLabels::orderStatus($o->status) : $o->status }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 fw-bold mb-0">Modül Yetkileri ve Abonelikler</h2>
        <a href="{{ route('vendor.subscriptions.index') }}" class="small fw-semibold text-decoration-none">Yönet →</a>
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted mb-1">Fiziksel Ürün</div>
                <span class="badge bg-success-subtle text-success">Aktif</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted mb-1">Freelancer</div>
                <span class="badge {{ $vendor->hasActiveFreelancerModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveFreelancerModule() ? 'Aktif' : 'Pasif' }}
                </span>
                <div class="small text-muted mt-2">{{ optional($vendor->freelancer_expires_at)->format('d.m.Y') ?? '-' }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted mb-1">Teklif</div>
                <span class="badge {{ $vendor->hasActiveQuotesModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveQuotesModule() ? 'Aktif' : 'Pasif' }}
                </span>
                <div class="small text-muted mt-2">{{ optional($vendor->quotes_expires_at)->format('d.m.Y') ?? '-' }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted mb-1">Tabela</div>
                <span class="badge {{ $vendor->hasActiveTabelaModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveTabelaModule() ? 'Aktif' : 'Pasif' }}
                </span>
                <div class="small text-muted mt-2">{{ optional($vendor->tabela_expires_at)->format('d.m.Y') ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.querySelectorAll('.metric-count[data-count]').forEach((el) => {
  const target = Number(el.getAttribute('data-count') || 0);
  if (!target || String(el.textContent).includes('₺')) return;
  let n = 0; const step = Math.max(1, Math.ceil(target / 30));
  const t = setInterval(() => { n = Math.min(target, n + step); el.textContent = n; if (n >= target) clearInterval(t); }, 20);
});
new Chart(document.getElementById('vendorRevenueChart'), {
  type: 'line',
  data: { labels: @json($revenueTrend['labels'] ?? []), datasets: [{ data: @json($revenueTrend['values'] ?? []), borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.15)', fill: true, tension: .35 }] },
  options: { plugins: { legend: { display: false } }, scales: { x: { display: false } } }
});
new Chart(document.getElementById('vendorStatusChart'), {
  type: 'doughnut',
  data: { labels: @json(($statusBreakdown ?? collect())->keys()), datasets: [{ data: @json(($statusBreakdown ?? collect())->values()), backgroundColor: ['#6366f1','#06b6d4','#84cc16','#f97316','#a855f7','#64748b'] }] },
  options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
