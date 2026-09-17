@extends('layouts.vendor')

@section('title', 'Genel Bakış - Satıcı Paneli')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-xs font-bold uppercase tracking-wider text-muted mb-1">Toplam Hakediş (Ciro)</div>
        <div class="text-2xl lg:text-3xl font-extrabold text-emerald-600 metric-count" data-count="{{ (int) $totalRevenue }}">
            ₺{{ number_format($totalRevenue, 2, ',', '.') }}
        </div>
        <a href="{{ route('vendor.payout-requests.index') }}" class="text-xs font-semibold text-emerald-700 hover:underline mt-2 inline-block">Para Çekme →</a>
    </div>

    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-xs font-bold uppercase tracking-wider text-muted mb-1">Aktif Siparişler</div>
        <div class="text-2xl lg:text-3xl font-extrabold text-ink metric-count" data-count="{{ $ordersPending }}">
            {{ $ordersPending }}
        </div>
        <a href="{{ route('vendor.orders.index') }}" class="text-xs font-semibold text-cta hover:underline mt-2 inline-block">Tümünü Yönet →</a>
    </div>

    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-xs font-bold uppercase tracking-wider text-muted mb-1">Prova Bekleyen</div>
        <div class="text-2xl lg:text-3xl font-extrabold text-amber-600 metric-count" data-count="{{ $proofPendingCount }}">
            {{ $proofPendingCount }}
        </div>
        <a href="{{ route('vendor.orders.index', ['status' => OrderStatus::DESIGN_REVIEW]) }}" class="text-xs font-semibold text-amber-700 hover:underline mt-2 inline-block">Provaları Yükle →</a>
    </div>

    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-xs font-bold uppercase tracking-wider text-muted mb-1">Yaklaşan Hakediş</div>
        <div class="text-2xl lg:text-3xl font-extrabold text-ink">
            ₺{{ number_format($upcomingPayouts ?? 0, 2, ',', '.') }}
        </div>
        <div class="text-[11px] text-muted mt-2">Kargolanacak: <strong class="text-ink">{{ $readyToShipCount }}</strong></div>
    </div>
</div>

@if(($moduleEnds ?? collect())->isNotEmpty())
    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-950">
        <span class="font-bold">Yaklaşan abonelik bitişleri:</span>
        @foreach($moduleEnds as $mod => $date)
            <span class="font-semibold">{{ $mod }}</span> ({{ $date->format('d.m.Y') }}){{ !$loop->last ? ', ' : '' }}
        @endforeach
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <div class="lg:col-span-8">
        <div class="by-card p-6 bg-surface border border-border h-full flex flex-col justify-between">
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <h2 class="font-heading text-lg font-bold text-ink">{{ $vendor->name }}</h2>
                        @if(!$vendor->is_active)
                            <x-badge variant="warning">Yönetici Onayı Bekliyor</x-badge>
                        @else
                            <x-badge variant="success">Aktif Mağaza</x-badge>
                        @endif
                    </div>
                    <a href="{{ route('vendors.show', $vendor->slug) }}" target="_blank" class="text-xs text-cta hover:underline font-medium">Mağaza Sayfasını Gör ↗</a>
                </div>
                <p class="text-xs text-muted leading-relaxed mb-3">
                    {{ $vendor->description ? Str::limit($vendor->description, 180) : 'Mağaza açıklaması henüz girilmedi.' }}
                </p>
                @if($vendor->businessTypes->isNotEmpty())
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        @foreach($vendor->businessTypes as $bt)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-canvas text-ink border border-border">{{ $bt->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="pt-3 border-t border-border flex items-center justify-between">
                <span class="text-xs text-muted">Kullanılabilir Bakiye:</span>
                <span class="text-lg font-bold text-ink">₺{{ number_format($vendor->balance, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="lg:col-span-4 flex flex-col gap-3">
        <a href="{{ route('vendor.products.create') }}" class="btn btn-cta py-3 text-xs flex items-center justify-center font-bold">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yeni Ürün Ekle
        </a>
        @if($vendor->hasActiveQuotesModule())
            <a href="{{ route('vendor.quote-requests.index') }}" class="btn btn-secondary py-3 text-xs flex items-center justify-center font-medium">
                Açık Teklif Talepleri ({{ $openQuoteRequestsCount }})
            </a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <div class="lg:col-span-7">
        <div class="by-card p-5 bg-surface border border-border h-full">
            <div class="text-xs font-bold uppercase tracking-wider text-muted mb-3">{{ __('panel.revenue_30d') }}</div>
            <canvas id="vendorRevenueChart" height="120"></canvas>
        </div>
    </div>
    <div class="lg:col-span-5">
        <div class="by-card p-5 bg-surface border border-border h-full">
            <div class="text-xs font-bold uppercase tracking-wider text-muted mb-3">{{ __('panel.order_status_chart') }}</div>
            <canvas id="vendorStatusChart" height="120"></canvas>
        </div>
    </div>
</div>

<div class="by-card bg-surface border border-border overflow-hidden mb-6">
    <div class="p-5 border-b border-border flex items-center justify-between">
        <h2 class="font-heading text-base font-bold text-ink">Son Siparişler</h2>
        <a href="{{ route('vendor.orders.index') }}" class="text-xs text-cta hover:underline font-semibold">Tümünü Gör →</a>
    </div>
    @if($recentOrders->isEmpty())
        <div class="p-8 text-center text-xs text-muted">Henüz sipariş bulunmuyor.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-border bg-canvas/50 text-muted uppercase text-[10px] font-semibold">
                        <th class="px-5 py-3">Sipariş No</th>
                        <th class="px-5 py-3">Müşteri</th>
                        <th class="px-5 py-3">Tutar</th>
                        <th class="px-5 py-3">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                @foreach($recentOrders as $o)
                    <tr class="hover:bg-canvas/30 transition-colors">
                        <td class="px-5 py-3.5 font-mono font-bold text-ink">
                            <a href="{{ route('vendor.orders.show', $o) }}" class="hover:text-cta">#{{ $o->order_number }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-ink font-medium">{{ $o->user?->name ?? 'Misafir Alıcı' }}</td>
                        <td class="px-5 py-3.5 font-bold text-ink">₺{{ number_format($o->vendor_amount ?? $o->subtotal, 2, ',', '.') }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-canvas text-ink border border-border">
                                {{ class_exists(UiLabels::class) ? UiLabels::orderStatus($o->status) : $o->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="by-card p-6 bg-surface border border-border">
    <div class="flex justify-between items-center mb-4 pb-3 border-b border-border">
        <h2 class="font-heading text-base font-bold text-ink">Modül Yetkileri ve Abonelikler</h2>
        <a href="{{ route('vendor.subscriptions.index') }}" class="text-xs text-cta hover:underline font-semibold">Yönet →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl border border-border bg-canvas/40">
            <div class="text-xs text-muted mb-1">Fiziksel Ürün</div>
            <x-badge variant="success">Aktif</x-badge>
        </div>
        <div class="p-4 rounded-xl border border-border bg-canvas/40">
            <div class="text-xs text-muted mb-1">Freelancer</div>
            <x-badge :variant="$vendor->hasActiveFreelancerModule() ? 'success' : 'warning'">
                {{ $vendor->hasActiveFreelancerModule() ? 'Aktif' : 'Pasif' }}
            </x-badge>
            <div class="text-[11px] text-muted mt-2">{{ optional($vendor->freelancer_expires_at)->format('d.m.Y') ?? '-' }}</div>
        </div>
        <div class="p-4 rounded-xl border border-border bg-canvas/40">
            <div class="text-xs text-muted mb-1">Teklif</div>
            <x-badge :variant="$vendor->hasActiveQuotesModule() ? 'success' : 'warning'">
                {{ $vendor->hasActiveQuotesModule() ? 'Aktif' : 'Pasif' }}
            </x-badge>
            <div class="text-[11px] text-muted mt-2">{{ optional($vendor->quotes_expires_at)->format('d.m.Y') ?? '-' }}</div>
        </div>
        <div class="p-4 rounded-xl border border-border bg-canvas/40">
            <div class="text-xs text-muted mb-1">Tabela</div>
            <x-badge :variant="$vendor->hasActiveTabelaModule() ? 'success' : 'warning'">
                {{ $vendor->hasActiveTabelaModule() ? 'Aktif' : 'Pasif' }}
            </x-badge>
            <div class="text-[11px] text-muted mt-2">{{ optional($vendor->tabela_expires_at)->format('d.m.Y') ?? '-' }}</div>
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
  data: { labels: @json($revenueTrend['labels'] ?? []), datasets: [{ data: @json($revenueTrend['values'] ?? []), borderColor: '#C2410C', backgroundColor: 'rgba(194,65,12,.1)', fill: true, tension: .35 }] },
  options: { plugins: { legend: { display: false } }, scales: { x: { display: false } } }
});
new Chart(document.getElementById('vendorStatusChart'), {
  type: 'doughnut',
  data: { labels: @json(($statusBreakdown ?? collect())->keys()), datasets: [{ data: @json(($statusBreakdown ?? collect())->values()), backgroundColor: ['#C2410C','#2563eb','#16a34a','#eab308','#8b5cf6','#64748b'] }] },
  options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
