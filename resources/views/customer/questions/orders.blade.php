@extends('layouts.account')
@section('title', 'Sipariş sorularım')
@section('content')
<div class="by-card p-5">
    <h1 class="mb-4 text-xl font-bold">Sipariş sorularım</h1>
    @if(session('success'))<p class="mb-3 text-sm text-emerald-700">{{ session('success') }}</p>@endif
    @forelse($questions as $q)
        @php $first = $q->order?->items?->first(); @endphp
        <div class="mb-3 rounded-2xl border border-slate-200 p-4">
            <div class="text-sm font-semibold">#{{ $q->order?->order_number }} · {{ $q->subject }}</div>
            @if($first)
                <div class="mt-1 text-xs text-slate-500">{{ $first->name }}</div>
            @endif
            @foreach($q->replies as $r)
                <p class="mt-2 rounded-xl p-3 text-sm {{ $r->is_from_vendor ? 'bg-slate-50' : 'bg-orange-50' }}">{{ $r->body }}</p>
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
