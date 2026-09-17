@extends('layouts.vendor')
@section('title', 'Teklif Talepleri - Satıcı Paneli')
@section('content')
<div class="mb-6">
    <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Açık Teklif Talepleri</h1>
    <p class="text-xs text-muted mt-0.5">Üretici kategorinize ve bölgenize uygun açık müşteri talepleri</p>
</div>

<div class="by-card bg-surface border border-border overflow-hidden">
    @if($quoteRequests->isEmpty())
        <div class="p-8 text-center text-xs text-muted">Şu anda size uygun açık teklif talebi bulunmuyor.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                        <th class="px-5 py-3">Talep</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Bölge</th>
                        <th class="px-5 py-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($quoteRequests as $qr)
                        <tr class="hover:bg-canvas/30 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-xs text-ink">{{ Str::limit($qr->title, 45) }}</td>
                            <td class="px-5 py-3.5 text-xs text-muted">{{ $qr->category?->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-xs text-muted">{{ $qr->city ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                @if($myQuotes->contains($qr->id))
                                    <x-badge variant="neutral">Teklif Verdiniz</x-badge>
                                @else
                                    <a href="{{ route('vendor.quote-requests.show', $qr) }}" class="btn btn-cta text-xs py-1.5 px-3">Teklif Ver →</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-border">{{ $quoteRequests->links() }}</div>
    @endif
</div>
@endsection
