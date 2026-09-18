@extends('layouts.admin')

@section('title', __('panel.dashboard'))

@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['label' => __('panel.shops'), 'value' => $metrics['vendors'] ?? 0, 'sub' => __('panel.active').': '.($metrics['active_vendors'] ?? 0), 'grad' => '#ecfdf5'],
        ['label' => __('panel.orders'), 'value' => $metrics['orders'] ?? 0, 'sub' => null, 'grad' => '#eff6ff'],
        ['label' => __('panel.revenue'), 'value' => '₺'.number_format($metrics['revenue'] ?? 0, 2, ',', '.'), 'sub' => null, 'grad' => '#fff7ed'],
        ['label' => __('panel.commission'), 'value' => '₺'.number_format($metrics['commission'] ?? 0, 2, ',', '.'), 'sub' => null, 'grad' => '#faf5ff'],
        ['label' => __('panel.upcoming_commission'), 'value' => '₺'.number_format($metrics['upcoming_commissions'] ?? 0, 2, ',', '.'), 'sub' => null, 'grad' => '#f0fdf4'],
        ['label' => __('panel.margin'), 'value' => '₺'.number_format($metrics['margin'] ?? 0, 2, ',', '.'), 'sub' => __('panel.expenses').': ₺'.number_format($metrics['platform_expenses'] ?? 0, 2, ',', '.'), 'grad' => '#fef2f2'],
    ] as $card)
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm metric-card" style="background: linear-gradient(135deg, {{ $card['grad'] }}, #fff);">
            <div class="small text-muted">{{ $card['label'] }}</div>
            <div class="h4 mb-0 fw-bold metric-count" data-count="{{ is_numeric($card['value']) ? $card['value'] : '' }}">{{ $card['value'] }}</div>
            @if($card['sub'])<div class="small text-muted">{{ $card['sub'] }}</div>@endif
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="{{ route('admin.product-approvals.index') }}" class="text-decoration-none">
            <div class="card p-3 border-0 shadow-sm h-100">
                <div class="small text-muted">Ürün onayları</div>
                <div class="h4 mb-0">{{ $pendingProductApprovals ?? 0 }}</div>
                <div class="small text-muted">Bekleyen</div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        @if(Route::has('admin.vendor-category-requests.index'))
        <a href="{{ route('admin.vendor-category-requests.index') }}" class="text-decoration-none">
            <div class="card p-3 border-0 shadow-sm h-100">
                <div class="small text-muted">Kategori talepleri</div>
                <div class="h4 mb-0">{{ $pendingCategoryRequests ?? 0 }}</div>
                <div class="small text-muted">Bekleyen</div>
            </div>
        </a>
        @endif
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.verifications.index') }}" class="text-decoration-none">
            <div class="card p-3 border-0 shadow-sm h-100">
                <div class="small text-muted">Belge onayları</div>
                <div class="h4 mb-0">{{ $pendingDocumentApprovals ?? 0 }}</div>
                <div class="small text-muted">Bekleyen</div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.vendor-payout-requests.index') }}" class="text-decoration-none">
            <div class="card p-3 border-0 shadow-sm h-100">
                <div class="small text-muted">Çekim talepleri</div>
                <div class="h4 mb-0">{{ $metrics['early_payout_requests'] ?? 0 }}</div>
                <div class="small text-muted">Onaylanana kadar durur</div>
            </div>
        </a>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm h-100">
            <div class="small text-muted">7 gün içinde biten modül</div>
            <div class="h4 mb-0">{{ $metrics['modules_expiring'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm h-100">
            <div class="small text-muted">Açık hava inceleme</div>
            <div class="h4 mb-0">{{ $metrics['outdoor_pending_review'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm h-100">
            <div class="small text-muted">KYC evrak kuyruğu</div>
            <div class="h4 mb-0">{{ $metrics['kyc_pending'] ?? 0 }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card p-3 border-0 shadow-sm" style="background:linear-gradient(135deg,#ecfdf5,#fff)"><div class="small text-muted">{{ __('panel.shipped') }}</div><div class="h5 mb-0 metric-count" data-count="{{ $metrics['shipped'] ?? 0 }}">{{ $metrics['shipped'] ?? 0 }}</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 border-0 shadow-sm"><div class="small text-muted">{{ __('panel.not_shipped') }}</div><div class="h5 mb-0 metric-count" data-count="{{ $metrics['not_shipped'] ?? 0 }}">{{ $metrics['not_shipped'] ?? 0 }}</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 border-0 shadow-sm"><div class="small text-muted">{{ __('panel.late_termin') }}</div><div class="h5 mb-0 text-danger metric-count" data-count="{{ $metrics['late_termin'] ?? 0 }}">{{ $metrics['late_termin'] ?? 0 }}</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 border-0 shadow-sm"><div class="small text-muted">{{ __('panel.early_payouts') }}</div><div class="h5 mb-0 metric-count" data-count="{{ $metrics['early_payout_requests'] ?? 0 }}">{{ $metrics['early_payout_requests'] ?? 0 }}</div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm h-100">
            <div class="small text-muted mb-2">{{ __('panel.risk_distribution') }}</div>
            <canvas id="riskChart" height="160"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm h-100">
            <div class="small text-muted mb-2">{{ __('panel.order_status_chart') }}</div>
            <canvas id="statusChart" height="160"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm h-100">
            <div class="small text-muted mb-2">{{ __('panel.revenue_30d') }}</div>
            <canvas id="revenueChart" height="160"></canvas>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4 shadow-sm border-0">
            <h2 class="h6 mb-3">{{ __('panel.recent_orders') }}</h2>
            @if($recentOrders->isEmpty())
                <p class="text-muted small mb-0">{{ __('panel.no_orders') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>No</th><th>{{ __('panel.customer') }}</th><th>{{ __('panel.vendor') }}</th><th>{{ __('panel.amount') }}</th><th>{{ __('panel.status') }}</th></tr></thead>
                        <tbody>
                            @foreach($recentOrders as $o)
                                <tr>
                                    <td class="small">#{{ $o->order_number }}</td>
                                    <td class="small">{{ $o->user?->name ?? '—' }} <code class="small">{{ $o->user?->publicCode() }}</code></td>
                                    <td class="small">{{ $o->vendor?->name ?? '—' }}</td>
                                    <td>₺{{ number_format($o->subtotal, 2, ',', '.') }}</td>
                                    <td><span class="badge bg-light text-dark">{{ \App\Support\UiLabels::orderStatus($o->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4 mb-4 shadow-sm border-0">
            <h2 class="h6 mb-3">{{ __('panel.risky_vendors') }}</h2>
            @forelse($riskyVendors as $v)
                <div class="d-flex justify-content-between small mb-2">
                    <a href="{{ route('admin.vendors.show', $v) }}">{{ $v->name }}</a>
                    <span class="badge bg-danger-subtle text-danger">{{ number_format($v->risk_score ?? 0, 1) }}</span>
                </div>
            @empty
                <p class="text-muted small mb-0">{{ __('panel.no_risky_vendors') }}</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.querySelectorAll('.metric-count[data-count]').forEach((el) => {
  const target = Number(el.getAttribute('data-count') || 0);
  if (!target) return;
  let n = 0; const step = Math.max(1, Math.ceil(target / 40));
  const t = setInterval(() => { n = Math.min(target, n + step); el.textContent = n; if (n >= target) clearInterval(t); }, 20);
});
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: { labels: @json($trend['labels'] ?? []), datasets: [{ data: @json($trend['values'] ?? []), borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.12)', fill: true, tension: .35 }] },
  options: { plugins: { legend: { display: false } }, scales: { x: { display: false } } }
});
new Chart(document.getElementById('riskChart'), {
  type: 'doughnut',
  data: { labels: ['{{ __('panel.risk_safe') }}','{{ __('panel.risk_medium') }}','{{ __('panel.risk_risky') }}'], datasets: [{ data: [{{ (int)($metrics['risk_safe']??0) }},{{ (int)($metrics['risk_medium']??0) }},{{ (int)($metrics['risk_risky']??0) }}], backgroundColor: ['#22c55e','#f59e0b','#ef4444'] }] },
  options: { plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('statusChart'), {
  type: 'doughnut',
  data: { labels: @json($statusBreakdown['labels'] ?? []), datasets: [{ data: @json($statusBreakdown['values'] ?? []), backgroundColor: ['#6366f1','#06b6d4','#84cc16','#f97316','#a855f7','#64748b','#14b8a6','#e11d48'] }] },
  options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
