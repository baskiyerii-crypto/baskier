@extends('layouts.account')

@section('title', 'Siparişlerim')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;
@endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="mb-0 text-lg font-bold">Siparişlerim</h1>
        <a href="{{ route('products.index') }}" class="rounded-full bg-amber-400 px-3 py-1.5 text-sm font-semibold text-slate-900">+ Alışverişe Başla</a>
    </div>

    @if(session('success'))
        <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="space-y-3">
        @forelse($orders as $o)
            @php
                $typeLabel = match($o->type) {
                    'product' => 'Pazaryeri',
                    'quote' => 'Özel Teklif',
                    'freelancer' => 'Freelancer',
                    default => ucfirst($o->type ?? 'Sipariş'),
                };
                $badgeClass = match($o->status) {
                    OrderStatus::CONFIRMED, 'paid' => 'bg-blue-600 text-white',
                    OrderStatus::PENDING, OrderStatus::PENDING_PAYMENT => 'bg-slate-700 text-white',
                    OrderStatus::DESIGN_REVIEW => 'bg-amber-200 text-amber-950',
                    OrderStatus::IN_PRODUCTION => 'bg-sky-200 text-sky-950',
                    OrderStatus::READY_TO_SHIP => 'bg-slate-200 text-slate-800',
                    OrderStatus::SHIPPED => 'bg-indigo-100 text-indigo-800',
                    OrderStatus::DELIVERED, OrderStatus::COMPLETED => 'bg-emerald-600 text-white',
                    OrderStatus::CANCELLED => 'bg-red-600 text-white',
                    default => 'bg-slate-600 text-white',
                };
                $firstItem = $o->items->first();
            @endphp
            <a href="{{ route('account.orders.show', $o) }}" class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm no-underline text-inherit hover:border-slate-300">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <div class="font-semibold text-slate-900">#{{ $o->order_number }}</div>
                        <div class="text-xs text-slate-500">{{ $o->created_at->format('d.m.Y H:i') }} · {{ $o->vendor?->name ?? 'BaskıYeri' }}</div>
                        @if($firstItem)
                            <div class="mt-1 text-sm text-slate-700">{{ $firstItem->name ?? 'Ürün' }}@if($o->items->count() > 1) <span class="text-slate-500">+{{ $o->items->count() - 1 }}</span>@endif</div>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass }}">{{ UiLabels::orderStatus($o->status) }}</span>
                        <div class="mt-1 text-xs text-slate-500">{{ $typeLabel }}</div>
                        <div class="mt-1 font-semibold text-emerald-700">₺{{ number_format($o->subtotal, 2, ',', '.') }}</div>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                Henüz verilmiş bir siparişiniz bulunmuyor.
            </div>
        @endforelse
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
@endsection
