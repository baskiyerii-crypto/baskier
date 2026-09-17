@extends('layouts.vendor')
@section('title', 'Bakiye & Cüzdan - Satıcı Paneli')
@section('content')
<div class="by-card p-6 bg-surface border border-border mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-muted">Tabela &amp; Teklif Görüşme Bakiyesi</h2>
            <div class="text-3xl font-extrabold text-ink mt-1">₺{{ number_format($vendor->balance, 2, ',', '.') }}</div>
            <p class="text-xs text-muted mt-1">Görüşme başına hizmet bedeli: <strong class="text-ink">₺{{ number_format($meetingFee, 2, ',', '.') }}</strong></p>
        </div>
        <form method="POST" action="{{ route('vendor.balance.topup') }}" class="flex items-end gap-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Yüklenecek Tutar (₺)</label>
                <input type="number" name="amount" min="10" max="10000" class="form-control text-xs w-28" value="100" required>
            </div>
            <button type="submit" class="btn btn-cta text-xs py-2 px-4">Bakiye Yükle</button>
        </form>
    </div>
</div>

<div class="by-card bg-surface border border-border overflow-hidden">
    <div class="p-5 border-b border-border">
        <h3 class="font-heading text-base font-bold text-ink">Bakiye Hareketleri</h3>
    </div>
    @if($transactions->isEmpty())
        <div class="p-8 text-center text-xs text-muted">Henüz bakiye hareketi bulunmuyor.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                        <th class="px-5 py-3">Tarih</th>
                        <th class="px-5 py-3">Açıklama</th>
                        <th class="px-5 py-3 text-right">Tutar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($transactions as $t)
                        <tr class="hover:bg-canvas/30 transition-colors">
                            <td class="px-5 py-3.5 text-xs text-muted">{{ $t->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-5 py-3.5 text-xs text-ink font-medium">{{ $t->description ?? $t->type }}</td>
                            <td class="px-5 py-3.5 text-xs font-bold text-right {{ $t->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $t->amount >= 0 ? '+' : '' }}₺{{ number_format($t->amount, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-border">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
