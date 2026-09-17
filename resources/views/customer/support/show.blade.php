@extends('layouts.account')

@section('title', $supportTicket->subject . ' - BaskıYeri')

@section('content')
@php use App\Support\UiLabels; @endphp
    <div class="mb-5">
        <a href="{{ route('account.support.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Taleplerime Dön
        </a>
    </div>

    <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
        <div>
            <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">{{ $supportTicket->subject }}</h1>
            <p class="text-xs text-muted mt-1">Talep No: #{{ $supportTicket->id }} · {{ $supportTicket->created_at->format('d.m.Y H:i') }}</p>
        </div>
        <div>
            @php
                $badgeVariant = match($supportTicket->status) {
                    'closed' => 'neutral',
                    'pending' => 'info',
                    default => 'warning',
                };
            @endphp
            <x-badge :variant="$badgeVariant">
                {{ UiLabels::supportTicketStatus($supportTicket->status) }}
            </x-badge>
        </div>
    </div>

    <div class="space-y-4 mb-6">
        @foreach($supportTicket->messages as $m)
            @php $isAdmin = str_starts_with($m->body, '[Yönetici]'); @endphp
            <div class="by-card p-5 border {{ $isAdmin ? 'bg-canvas border-cta/30' : 'bg-surface border-border' }}">
                <div class="flex justify-between items-center text-xs text-muted mb-2">
                    <strong class="text-ink font-semibold {{ $isAdmin ? 'text-cta' : '' }}">{{ $m->user?->name ?? ($isAdmin ? 'BaskıYeri Destek' : 'Kullanıcı') }}</strong>
                    <span>{{ $m->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <div class="text-xs text-ink whitespace-pre-wrap leading-relaxed">{{ preg_replace('/^\[Yönetici\]\s*/', '', $m->body) }}</div>
            </div>
        @endforeach
    </div>

    @if($supportTicket->status !== 'closed')
        <div class="by-card p-6 bg-surface border border-border">
            <h2 class="font-heading text-base font-bold text-ink mb-3">Yanıt Yaz</h2>
            <form method="post" action="{{ route('account.support.reply', $supportTicket) }}" class="space-y-3">
                @csrf
                <textarea name="body" rows="4" class="form-control text-xs @error('body') border-red-500 @enderror" required maxlength="5000" placeholder="Mesajınız..."></textarea>
                @error('body')<div class="text-red-500 text-[11px]">{{ $message }}</div>@enderror
                <button type="submit" class="btn btn-cta text-xs py-2 px-6">Yanıtı Gönder</button>
            </form>
        </div>
    @else
        <div class="by-card p-4 bg-canvas border border-border text-center text-xs text-muted">
            Bu destek talebi kapatılmıştır. Yeni bir konunuz varsa yeni talep açabilirsiniz.
        </div>
    @endif
@endsection
