@extends('layouts.account')
@section('title', __('panel.direct_quotes') . ' - BaskıYeri')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">{{ __('panel.direct_quotes') }}</h1>
        <p class="text-xs text-muted mt-0.5">Daha önce alışveriş yaptığınız üreticilere doğrudan ilettiğiniz özel teklifler</p>
    </div>
    <a href="{{ route('customer.direct-quotes.create') }}" class="btn btn-cta text-xs">{{ __('panel.new_direct_quote') }}</a>
</div>

<div class="by-card bg-surface border border-border overflow-hidden">
    @forelse($items as $item)
        <a href="{{ route('customer.direct-quotes.show', $item) }}" class="block border-b border-border last:border-0 p-4 hover:bg-canvas/50 transition-colors">
            <div class="flex items-center justify-between gap-3">
                <div class="font-semibold text-sm text-ink">{{ $item->title }}</div>
                <x-badge variant="neutral">{{ \App\Support\UiLabels::status($item->status) }}</x-badge>
            </div>
            <div class="text-xs text-muted mt-1">{{ $item->vendor?->name }} · {{ $item->created_at->format('d.m.Y') }}</div>
        </a>
    @empty
        <div class="p-8 text-center text-xs text-muted">{{ __('panel.no_results') }}</div>
    @endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
