@extends('layouts.admin')

@section('title', __('panel.dashboard') . ' - Yönetim Paneli')

@section('content')
<div class="mb-6">
    <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">{{ __('panel.dashboard') }}</h1>
    <p class="text-xs text-muted mt-0.5">Pazaryeri genel durumu, sipariş hacmi, komisyon gelirleri ve satıcı risk analizi</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @foreach([
        ['label' => __('panel.shops'), 'value' => $metrics['vendors'] ?? 0, 'sub' => __('panel.active').': '.($metrics['active_vendors'] ?? 0), 'is_num' => true],
        ['label' => __('panel.orders'), 'value' => $metrics['orders'] ?? 0, 'sub' => null, 'is_num' => true],
        ['label' => __('panel.revenue'), 'value' => '₺'.number_format($metrics['revenue'] ?? 0, 2, ',', '.'), 'sub' => null, 'is_num' => false],
        ['label' => __('panel.commission'), 'value' => '₺'.number_format($metrics['commission'] ?? 0, 2, ',', '.'), 'sub' => null, 'is_num' => false],
        ['label' => __('panel.upcoming_commission'), 'value' => '₺'.number_format($metrics['upcoming_commissions'] ?? 0, 2, ',', '.'), 'sub' => null, 'is_num' => false],
        ['label' => __('panel.margin'), 'value' => '₺'.number_format($metrics['margin'] ?? 0, 2, ',', '.'), 'sub' => __('panel.expenses').': ₺'.number_format($metrics['platform_expenses'] ?? 0, 2, ',', '.'), 'is_num' => false],
    ] as $card)
    <div class="by-card p-4 bg-surface border border-border flex flex-col justify-between">
        <div class="text-[11px] font-bold uppercase tracking-wider text-muted mb-1">{{ $card['label'] }}</div>
        <div class="text-xl lg:text-2xl font-extrabold text-ink metric-count" @if($card['is_num']) data-count="{{ $card['value'] }}" @endif>{{ $card['value'] }}</div>
        @if($card['sub'])<div class="text-[10px] text-muted mt-1">{{ $card['sub'] }}</div>@endif
    </div>
    @endforeach
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="by-card p-4 bg-surface border border-border">
        <div class="text-[11px] font-bold uppercase tracking-wider text-muted mb-1">{{ __('panel.shipped') }}</div>
        <div class="text-xl font-bold text-ink metric-count" data-count="{{ $metrics['shipped'] ?? 0 }}">{{ $metrics['shipped'] ?? 0 }}</div>
    </div>
    <div class="by-card p-4 bg-surface border border-border">
        <div class="text-[11px] font-bold uppercase tracking-wider text-muted mb-1">{{ __('panel.not_shipped') }}</div>
        <div class="text-xl font-bold text-ink metric-count" data-count="{{ $metrics['not_shipped'] ?? 0 }}">{{ $metrics['not_shipped'] ?? 0 }}</div>
    </div>
    <div class="by-card p-4 bg-surface border border-border">
        <div class="text-[11px] font-bold uppercase tracking-wider text-muted mb-1">{{ __('panel.late_termin') }}</div>
        <div class="text-xl font-bold text-red-600 metric-count" data-count="{{ $metrics['late_termin'] ?? 0 }}">{{ $metrics['late_termin'] ?? 0 }}</div>
    </div>
    <div class="by-card p-4 bg-surface border border-border">
        <div class="text-[11px] font-bold uppercase tracking-wider text-muted mb-1">{{ __('panel.early_payouts') }}</div>
        <div class="text-xl font-bold text-ink metric-count" data-count="{{ $metrics['early_payout_requests'] ?? 0 }}">{{ $metrics['early_payout_requests'] ?? 0 }}</div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-xs font-bold uppercase tracking-wider text-muted mb-3">{{ __('panel.risk_distribution') }}</div>
        <canvas id="riskChart" height="160"></canvas>
    </div>
    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-xs font-bold uppercase tracking-wider text-muted mb-3">{{ __('panel.order_status_chart') }}</div>
        <canvas id="statusChart" height="160"></canvas>
    </div>
    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-xs font-bold uppercase tracking-wider text-muted mb-3">{{ __('panel.revenue_30d') }}</div>
        <canvas id="revenueChart" height="160"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8">
        <div class="by-card bg-surface border border-border overflow-hidden h-full flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-border">
                    <h2 class="font-heading text-base font-bold text-ink">{{ __('panel.recent_orders') }}</h2>
                </div>
                @if($recentOrders->isEmpty())
                    <div class="p-8 text-center text-xs text-muted">{{ __('panel.no_orders') }}</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                                    <th class="px-5 py-3">No</th>
                                    <th class="px-5 py-3">{{ __('panel.customer') }}</th>
                                    <th class="px-5 py-3">{{ __('panel.vendor') }}</th>
                                    <th class="px-5 py-3">Tutar</th>
                                    <th class="px-5 py-3 text-right">{{ __('panel.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($recentOrders as $o)
                                    <tr class="hover:bg-canvas/30 transition-colors">
                                        <td class="px-5 py-3.5 font-mono font-bold text-xs text-ink">#{{ $o->order_number }}</td>
                                        <td class="px-5 py-3.5 text-xs text-ink">
                                            {{ $o->user?->name ?? '—' }} 
                                            <span class="text-[10px] font-mono text-muted">({{ $o->user?->publicCode() }})</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-muted">{{ $o->vendor?->name ?? '—' }}</td>
                                        <td class="px-5 py-3.5 text-xs font-bold text-ink">₺{{ number_format($o->subtotal, 2, ',', '.') }}</td>
                                        <td class="px-5 py-3.5 text-right">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-canvas text-ink border border-border">
                                                {{ \App\Support\UiLabels::orderStatus($o->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="lg:col-span-4">
        <div class="by-card p-5 bg-surface border border-border h-full">
            <h2 class="font-heading text-base font-bold text-ink mb-4 pb-2 border-b border-border">{{ __('panel.risky_vendors') }}</h2>
            <div class="space-y-3">
                @forelse($riskyVendors as $v)
                    <div class="flex items-center justify-between p-3 rounded-lg border border-border bg-canvas/40 text-xs">
                        <a href="{{ route('admin.vendors.show', $v) }}" class="font-semibold text-ink hover:text-cta">{{ $v->name }}</a>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">
                            {{ number_format($v->risk_score ?? 0, 1) }}
                        </span>
                    </div>
                @empty
                    <div class="text-center text-xs text-muted py-6">{{ __('panel.no_risky_vendors') }}</div>
                @endforelse
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
  if (!target) return;
  let n = 0; const step = Math.max(1, Math.ceil(target / 40));
  const t = setInterval(() => { n = Math.min(target, n + step); el.textContent = n; if (n >= target) clearInterval(t); }, 20);
});
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: { labels: @json($trend['labels'] ?? []), datasets: [{ data: @json($trend['values'] ?? []), borderColor: '#C2410C', backgroundColor: 'rgba(194,65,12,.12)', fill: true, tension: .35 }] },
  options: { plugins: { legend: { display: false } }, scales: { x: { display: false } } }
});
new Chart(document.getElementById('riskChart'), {
  type: 'doughnut',
  data: { labels: ['{{ __('panel.risk_safe') }}','{{ __('panel.risk_medium') }}','{{ __('panel.risk_risky') }}'], datasets: [{ data: [{{ (int)($metrics['risk_safe']??0) }},{{ (int)($metrics['risk_medium']??0) }},{{ (int)($metrics['risk_risky']??0) }}], backgroundColor: ['#16a34a','#eab308','#dc2626'] }] },
  options: { plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('statusChart'), {
  type: 'doughnut',
  data: { labels: @json($statusBreakdown['labels'] ?? []), datasets: [{ data: @json($statusBreakdown['values'] ?? []), backgroundColor: ['#C2410C','#2563eb','#16a34a','#eab308','#8b5cf6','#64748b','#0d9488','#e11d48'] }] },
  options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
