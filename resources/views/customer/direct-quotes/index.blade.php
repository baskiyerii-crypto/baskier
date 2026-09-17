@extends('layouts.account')
@section('title', __('panel.direct_quotes'))
@section('content')
<div class="flex justify-between mb-4">
    <h1 class="text-xl font-bold">{{ __('panel.direct_quotes') }}</h1>
    <a href="{{ route('customer.direct-quotes.create') }}" class="by-btn-primary">{{ __('panel.new_direct_quote') }}</a>
</div>
<div class="by-card overflow-hidden">
    @forelse($items as $item)
        <a href="{{ route('customer.direct-quotes.show', $item) }}" class="block border-b px-4 py-3 hover:bg-slate-50">
            <div class="font-semibold">{{ $item->title }}</div>
            <div class="text-sm text-slate-500">{{ $item->vendor?->name }} · {{ \App\Support\UiLabels::status($item->status) }}</div>
        </a>
    @empty
        <p class="p-4 text-sm text-slate-500">{{ __('panel.no_results') }}</p>
    @endforelse
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
