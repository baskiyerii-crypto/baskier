@extends('layouts.admin')
@section('title', 'Finans & Muhasebe - Yönetim Paneli')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Finans &amp; Gelir Yönetimi</h1>
        <p class="text-xs text-muted mt-0.5">Platform cirosu, komisyon hakedişleri, abonelik gelirleri ve giderler</p>
    </div>
    <div>
        <a href="{{ route('admin.finance.export') }}" class="btn btn-secondary text-xs">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            CSV Olarak Dışa Aktar
        </a>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-[11px] font-bold uppercase tracking-wider text-muted mb-1">Toplam Ciro</div>
        <div class="text-xl lg:text-2xl font-extrabold text-ink">₺{{ number_format($metrics['revenue'], 2, ',', '.') }}</div>
    </div>
    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-[11px] font-bold uppercase tracking-wider text-muted mb-1">Komisyon Geliri</div>
        <div class="text-xl lg:text-2xl font-extrabold text-emerald-600">₺{{ number_format($metrics['commission'], 2, ',', '.') }}</div>
    </div>
    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-[11px] font-bold uppercase tracking-wider text-muted mb-1">Abonelik Geliri</div>
        <div class="text-xl lg:text-2xl font-extrabold text-sky-600">₺{{ number_format($metrics['subscription_income'], 2, ',', '.') }}</div>
    </div>
    <div class="by-card p-5 bg-surface border border-border">
        <div class="text-[11px] font-bold uppercase tracking-wider text-muted mb-1">Net Kar Marjı</div>
        <div class="text-xl lg:text-2xl font-extrabold text-ink">₺{{ number_format($metrics['margin'], 2, ',', '.') }}</div>
    </div>
</div>

<div class="by-card p-5 bg-surface border border-border mb-6 max-w-md">
    <h2 class="font-heading text-sm font-bold text-ink mb-2">Platform Giderleri</h2>
    <p class="text-xs text-muted mb-3">Sunucu, lisans ve operasyon giderleri toplamı</p>
    <form method="post" action="{{ route('admin.finance.expenses') }}" class="flex gap-2">
        @csrf
        <input type="number" step="0.01" min="0" name="platform_expenses" class="form-control text-xs" value="{{ $metrics['platform_expenses'] }}">
        <button class="btn btn-cta text-xs py-1.5 px-4">Kaydet</button>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="by-card bg-surface border border-border overflow-hidden flex flex-col justify-between">
        <div>
            <div class="p-5 border-b border-border">
                <h2 class="font-heading text-base font-bold text-ink">Gelen Müşteri Ödemeleri</h2>
            </div>
            <div class="p-5 space-y-3">
                @forelse($payments as $p)
                    <div class="flex items-center justify-between text-xs border-b border-border pb-2.5 last:border-0">
                        <div>
                            <span class="font-mono font-semibold text-ink">#{{ $p->order?->order_number }}</span>
                            <span class="text-muted">· {{ $p->provider }}</span>
                            <span class="ml-1"><x-badge variant="neutral">{{ \App\Support\UiLabels::status($p->status) }}</x-badge></span>
                        </div>
                        <span class="font-bold text-ink">₺{{ number_format($p->amount, 2, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-xs text-muted text-center py-4">Ödeme kaydı bulunmuyor.</p>
                @endforelse
            </div>
        </div>
        <div class="p-4 border-t border-border">{{ $payments->links() }}</div>
    </div>

    <div class="by-card bg-surface border border-border overflow-hidden flex flex-col justify-between">
        <div>
            <div class="p-5 border-b border-border">
                <h2 class="font-heading text-base font-bold text-ink">Satıcı Bakiye Hareketleri</h2>
            </div>
            <div class="p-5 space-y-3">
                @forelse($transactions as $tx)
                    <div class="flex items-center justify-between text-xs border-b border-border pb-2.5 last:border-0">
                        <div>
                            <strong class="text-ink">{{ $tx->vendor?->name }}</strong>
                            <div class="text-[11px] text-muted">{{ $tx->description }}</div>
                        </div>
                        <span class="font-bold text-ink">₺{{ number_format($tx->amount, 2, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-xs text-muted text-center py-4">Hareket bulunmuyor.</p>
                @endforelse
            </div>
        </div>
        <div class="p-4 border-t border-border">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
