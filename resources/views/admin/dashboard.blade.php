@extends('layouts.admin')

@section('title', 'Genel bakış')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Mağaza</div>
            <div class="h4 mb-0">{{ $metrics['vendors'] ?? $stats['vendors'] }}</div>
            <div class="small text-muted">Aktif: {{ $metrics['active_vendors'] ?? '-' }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Toplam sipariş</div>
            <div class="h4 mb-0">{{ $metrics['orders'] ?? $stats['orders'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Ciro</div>
            <div class="h5 mb-0">₺{{ number_format($metrics['revenue'] ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Komisyon</div>
            <div class="h5 mb-0">₺{{ number_format($metrics['commission'] ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Yaklaşan komisyon</div>
            <div class="h5 mb-0">₺{{ number_format($metrics['upcoming_commissions'] ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Kar marjı</div>
            <div class="h5 mb-0">₺{{ number_format($metrics['margin'] ?? 0, 2, ',', '.') }}</div>
            <div class="small text-muted">Gider: ₺{{ number_format($metrics['platform_expenses'] ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3 border-0 shadow-sm"><div class="small text-muted">Kargolanan</div><div class="h5 mb-0">{{ $metrics['shipped'] ?? 0 }}</div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 border-0 shadow-sm"><div class="small text-muted">Kargolanmayan</div><div class="h5 mb-0">{{ $metrics['not_shipped'] ?? 0 }}</div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 border-0 shadow-sm"><div class="small text-muted">Termin gecikmiş</div><div class="h5 mb-0 text-danger">{{ $metrics['late_termin'] ?? 0 }}</div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 border-0 shadow-sm"><div class="small text-muted">Erken ödeme talebi</div><div class="h5 mb-0">{{ $metrics['early_payout_requests'] ?? 0 }}</div></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm">
            <div class="small text-muted">Satıcı risk dağılımı</div>
            <div class="d-flex gap-2 mt-2 flex-wrap">
                <span class="badge bg-success-subtle text-success">Risksiz: {{ $metrics['risk_safe'] ?? 0 }}</span>
                <span class="badge bg-warning text-dark">Orta: {{ $metrics['risk_medium'] ?? 0 }}</span>
                <span class="badge bg-danger-subtle text-danger">Riskli: {{ $metrics['risk_risky'] ?? 0 }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3 border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h6 mb-0">30 günlük ciro</h2>
            </div>
            <canvas id="revenueChart" height="90"></canvas>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <h2 class="h6 mb-3">Son siparişler</h2>
            @if($recentOrders->isEmpty())
                <p class="text-muted small mb-0">Henüz sipariş yok.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>No</th><th>Müşteri</th><th>Satıcı</th><th>Tutar</th><th>Durum</th></tr></thead>
                        <tbody>
                            @foreach($recentOrders as $o)
                                <tr>
                                    <td class="small">#{{ $o->order_number }}</td>
                                    <td class="small">{{ $o->user?->name ?? '—' }}</td>
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
        <div class="card p-4 mb-4">
            <h2 class="h6 mb-3">Riskli satıcılar</h2>
            @forelse($riskyVendors as $v)
                <div class="d-flex justify-content-between small mb-2">
                    <a href="{{ route('admin.vendors.show', $v) }}">{{ $v->name }}</a>
                    <span class="badge bg-danger-subtle text-danger">{{ number_format($v->risk_score ?? 0, 1) }}</span>
                </div>
            @empty
                <p class="text-muted small mb-0">Riskli satıcı yok.</p>
            @endforelse
        </div>
        <div class="card p-4">
            <h2 class="h6 mb-3">Son ürünler</h2>
            @if($recentProducts->isEmpty())
                <p class="text-muted small mb-0">Henüz ürün yok.</p>
            @else
                <ul class="list-unstyled mb-0 small">
                    @foreach($recentProducts->take(6) as $p)
                        <li class="mb-2">{{ $p->name }} <span class="text-muted">· {{ $p->vendor?->name }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('revenueChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($trend['labels'] ?? []),
            datasets: [{
                label: 'Ciro',
                data: @json($trend['values'] ?? []),
                borderColor: '#059669',
                backgroundColor: 'rgba(5,150,105,.12)',
                fill: true,
                tension: .35
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { x: { display: false } } }
    });
}
</script>
@endpush
