@extends('layouts.account')
@section('title', 'Konuşma')
@section('content')
<div class="by-card p-5">
    <a href="{{ route('customer.messages.index') }}" class="text-sm text-slate-500">← Mesajlar</a>
    <h1 class="text-xl font-bold mt-2">{{ $conversation->vendor?->name }}</h1>
    <div class="mt-4 space-y-3 max-h-[480px] overflow-y-auto">
        @foreach($conversation->messages as $m)
            <div class="rounded-2xl p-3 {{ $m->is_from_vendor ? 'bg-slate-50' : 'bg-orange-50' }}">
                <div class="text-xs text-slate-500">{{ $m->is_from_vendor ? 'Satıcı' : 'Siz' }} · {{ $m->created_at?->format('d.m.Y H:i') }}</div>
                <p class="text-sm mt-1 whitespace-pre-wrap">{{ $m->blocked ? ($m->display_body ?: 'Mesaj gizlendi') : $m->body }}</p>
            </div>
        @endforeach
    </div>
    <form method="POST" action="{{ route('customer.messages.store', $conversation) }}" class="mt-4">
        @csrf
        <textarea name="body" class="w-full rounded-2xl border border-slate-200 p-3 text-sm" rows="3" required maxlength="2000" placeholder="Mesaj yazın. Telefon, link ve sosyal medya paylaşımı yasaktır."></textarea>
        <button class="by-btn-primary mt-2">Gönder</button>
    </form>
</div>
@endsection
