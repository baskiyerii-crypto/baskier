@extends('layouts.app')

@section('title', 'Hizmet Taleplerim - BaskıYeri')

@section('content')
<div class="by-container py-8 md:py-12">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink">Hizmet Taleplerim</h1>
            <p class="text-sm text-muted mt-1">Açtığınız tasarım ve hazırlık talepleri ve uzman teklifleri</p>
        </div>
        <div>
            <a href="{{ route('service-requests.create') }}" class="btn btn-cta text-sm">
                + Yeni Hizmet Talebi
            </a>
        </div>
    </div>

    @if($jobs->isEmpty())
        <div class="by-card p-10 text-center bg-surface border border-border">
            <div class="w-12 h-12 mx-auto rounded-full bg-canvas flex items-center justify-center text-muted mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h3 class="text-base font-bold text-ink">Henüz bir hizmet talebiniz bulunmuyor</h3>
            <p class="text-sm text-muted mt-1 max-w-md mx-auto">İhtiyacınız olan tasarım işleri için talep oluşturup teklif toplayabilirsiniz.</p>
            <div class="mt-4">
                <a href="{{ route('service-requests.create') }}" class="btn btn-cta text-xs">Talep Oluştur</a>
            </div>
        </div>
    @else
        <div class="by-card bg-surface overflow-hidden border border-border">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                            <th class="px-5 py-3.5">Başlık</th>
                            <th class="px-5 py-3.5">Durum</th>
                            <th class="px-5 py-3.5">Gelen Teklif</th>
                            <th class="px-5 py-3.5 text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($jobs as $j)
                            <tr class="hover:bg-canvas/40 transition-colors">
                                <td class="px-5 py-4 font-medium text-ink">
                                    <a href="{{ route('service-requests.show', $j) }}" class="hover:text-cta font-medium">
                                        {{ Str::limit($j->title, 50) }}
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge variant="neutral">{{ \App\Support\UiLabels::status($j->status) }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold text-ink">
                                    {{ $j->bids_count }} Teklif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('service-requests.show', $j) }}" class="btn btn-secondary text-xs py-1.5 px-3">
                                        İncele
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-5">
            {{ $jobs->links() }}
        </div>
    @endif
</div>
@endsection
