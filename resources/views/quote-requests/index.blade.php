@extends('layouts.app')
@section('title', 'Teklif Taleplerim - BaskıYeri')
@section('content')
<div class="by-container py-8 md:py-12">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink">Teklif Taleplerim</h1>
            <p class="text-sm text-muted mt-1">Özel üretim ve baskı taleplerinizin durumu ve gelen üretici teklifleri</p>
        </div>
        <div>
            <a href="{{ route('quote-requests.create') }}" class="btn btn-cta text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Yeni Talep Oluştur
            </a>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    @if($requests->isEmpty())
        <div class="by-card p-10 text-center bg-surface border border-border">
            <div class="w-12 h-12 mx-auto rounded-full bg-canvas flex items-center justify-center text-muted mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-base font-bold text-ink">Henüz teklif talebiniz yok</h3>
            <p class="text-sm text-muted mt-1 max-w-md mx-auto">İhtiyacınız olan baskı ve özel üretim işleri için hemen üreticilerden fiyat teklifi toplayabilirsiniz.</p>
            <div class="mt-4">
                <a href="{{ route('quote-requests.create') }}" class="btn btn-cta text-xs">Teklif İste</a>
            </div>
        </div>
    @else
        <div class="by-card bg-surface overflow-hidden border border-border">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                            <th class="px-5 py-3.5">Başlık</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5">Durum</th>
                            <th class="px-5 py-3.5">Tarih</th>
                            <th class="px-5 py-3.5 text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($requests as $r)
                            <tr class="hover:bg-canvas/40 transition-colors">
                                <td class="px-5 py-4 font-medium text-ink">
                                    <a href="{{ route('quote-requests.show', $r) }}" class="hover:text-cta font-medium">
                                        {{ Str::limit($r->title, 45) }}
                                    </a>
                                </td>
                                <td class="px-5 py-4 text-muted text-xs">
                                    @if($r->items && $r->items->isNotEmpty())
                                        {{ $r->items->first()->category?->name ?? $r->category?->name }}
                                        @if($r->items->pluck('category_id')->unique()->count() > 1)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-canvas text-muted border border-border ml-1">+{{ $r->items->pluck('category_id')->unique()->count() - 1 }}</span>
                                        @endif
                                    @else
                                        {{ $r->category?->name }}
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($r->status === 'open')
                                        <x-badge variant="success">{{ __('panel.status_open') }}</x-badge>
                                    @else
                                        <x-badge variant="neutral">{{ __('panel.status_closed') }}</x-badge>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-muted">
                                    {{ $r->created_at->format('d.m.Y') }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('quote-requests.show', $r) }}" class="btn btn-secondary text-xs py-1.5 px-3">
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
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
