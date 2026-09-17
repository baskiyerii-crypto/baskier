@extends('layouts.account')
@section('title', 'Mesajlar')
@section('content')
<div class="by-card p-5">
    <h1 class="text-xl font-bold mb-4">Mesajlar</h1>
    @forelse($conversations as $c)
        <a href="{{ route('customer.messages.show', $c) }}" class="block rounded-2xl border border-slate-200 p-4 mb-2 hover:bg-slate-50">
            <div class="font-semibold text-slate-900">{{ $c->vendor?->name ?? 'Satıcı' }}</div>
            <div class="text-xs text-slate-500">{{ $c->messages_count }} mesaj · {{ optional($c->last_message_at)->format('d.m.Y H:i') }}</div>
        </a>
    @empty
        <p class="text-sm text-slate-500">Henüz konuşma yok.</p>
    @endforelse
    <div class="mt-4">{{ $conversations->links() }}</div>
</div>
@endsection
