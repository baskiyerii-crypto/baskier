@extends('layouts.account')
@section('title', 'Sipariş sorularım')
@section('content')
<div class="by-card p-5">
    <h1 class="text-xl font-bold mb-4">Sipariş sorularım</h1>
    @forelse($questions as $q)
        <div class="rounded-2xl border border-slate-200 p-4 mb-3">
            <div class="text-sm font-semibold">#{{ $q->order?->order_number }} · {{ $q->subject }}</div>
            @foreach($q->replies as $r)
                <p class="text-sm mt-2 {{ $r->is_from_vendor ? 'bg-slate-50' : 'bg-orange-50' }} rounded-xl p-3">{{ $r->body }}</p>
            @endforeach
            <form method="POST" action="{{ route('customer.order-questions.reply', $q) }}" class="mt-3">
                @csrf
                <textarea name="body" class="w-full rounded-xl border p-2 text-sm" rows="2" required></textarea>
                <button class="by-btn-secondary mt-2 text-sm">Yanıtla</button>
            </form>
        </div>
    @empty
        <p class="text-sm text-slate-500">Soru yok.</p>
    @endforelse
    {{ $questions->links() }}
</div>
@endsection
