@extends('layouts.admin')
@section('title', 'Hakediş Onayları - Yönetim Paneli')
@section('content')
<div class="mb-6">
    <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Hakediş &amp; Ödeme Onayları</h1>
    <p class="text-xs text-muted mt-0.5">Teslimatı tamamlanan siparişlerin satıcı hakediş transfer onayları</p>
</div>

<div class="by-card p-6 bg-surface border border-border mb-6">
    <h2 class="font-heading text-base font-bold text-ink mb-4 pb-2 border-b border-border">Hakedişe Hazır Satıcılar</h2>
    @if($grouped->isEmpty())
        <p class="text-xs text-muted mb-0 py-4 text-center">Şu anda onay bekleyen hakediş bulunmuyor.</p>
    @else
        <div class="space-y-3">
            @foreach($grouped as $g)
                <div class="p-4 rounded-xl border border-border bg-canvas/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div>
                        <strong class="text-sm font-bold text-ink">{{ $g['vendor']->name }}</strong>
                        <div class="text-muted mt-0.5">
                            Satıcıya Ödenecek: <strong class="text-emerald-700">₺{{ number_format($g['total_vendor_amount'], 2, ',', '.') }}</strong>
                            · Komisyon: <span class="text-ink">₺{{ number_format($g['total_commission'], 2, ',', '.') }}</span>
                            · <span class="text-muted">{{ count($g['orders']) }} sipariş</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.payouts.approve') }}" class="inline">
                        @csrf
                        @foreach($g['orders'] as $o)
                            <input type="hidden" name="order_ids[]" value="{{ $o->id }}">
                        @endforeach
                        <button type="submit" class="btn btn-cta text-xs py-1.5 px-4 font-bold">Hakedişi Onayla</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="by-card bg-surface border border-border overflow-hidden">
    <div class="p-5 border-b border-border">
        <h3 class="font-heading text-base font-bold text-ink">Son Onaylanan Hakedişler</h3>
    </div>
    @if($recentlyApproved->isEmpty())
        <div class="p-8 text-center text-xs text-muted">Henüz onaylanmış hakediş kaydı bulunmuyor.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                        <th class="px-5 py-3">Sipariş No</th>
                        <th class="px-5 py-3">Satıcı</th>
                        <th class="px-5 py-3 text-right">Onay Tarihi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($recentlyApproved as $o)
                        <tr class="hover:bg-canvas/30 transition-colors">
                            <td class="px-5 py-3.5 font-mono font-bold text-xs text-ink">#{{ $o->order_number }}</td>
                            <td class="px-5 py-3.5 text-xs text-ink">{{ $o->vendor?->name }}</td>
                            <td class="px-5 py-3.5 text-xs text-muted text-right">{{ $o->payout_at?->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
