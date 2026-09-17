@extends('layouts.account')

@section('title', 'Destek Taleplerim - BaskıYeri')

@section('content')
@php use App\Support\UiLabels; @endphp
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Destek Taleplerim</h1>
            <p class="text-xs text-muted mt-0.5">Sipariş, baskı ve platform ile ilgili soru ve bildirimleriniz</p>
        </div>
        <div>
            <a href="{{ route('account.support.create') }}" class="btn btn-cta text-xs">
                + Yeni Destek Talebi
            </a>
        </div>
    </div>

    @if($tickets->isEmpty())
        <div class="by-card p-12 text-center bg-surface border border-border">
            <div class="w-12 h-12 mx-auto rounded-full bg-canvas flex items-center justify-center text-muted mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <h3 class="text-base font-bold text-ink">Henüz bir destek talebiniz bulunmuyor</h3>
            <p class="text-sm text-muted mt-1 max-w-md mx-auto">Bir sorunuz veya yardıma ihtiyacınız olduğunda ekibimize buradan hemen yazabilirsiniz.</p>
            <div class="mt-4">
                <a href="{{ route('account.support.create') }}" class="btn btn-cta text-xs">Talep Oluştur</a>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @foreach($tickets as $t)
                @php
                    $badgeVariant = match($t->status) {
                        'closed' => 'neutral',
                        'pending' => 'info',
                        default => 'warning',
                    };
                @endphp
                <a href="{{ route('account.support.show', $t) }}" class="by-card p-5 bg-surface border border-border flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-cta/50 transition-colors block">
                    <div>
                        <div class="font-bold text-sm text-ink hover:text-cta transition-colors">{{ $t->subject }}</div>
                        <div class="text-xs text-muted mt-1">{{ $t->created_at->translatedFormat('d F Y, H:i') }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge :variant="$badgeVariant">
                            {{ UiLabels::supportTicketStatus($t->status) }}
                        </x-badge>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-5">{{ $tickets->links() }}</div>
    @endif
@endsection
