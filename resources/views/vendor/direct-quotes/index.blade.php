@extends('layouts.vendor')
@section('title', __('panel.direct_quotes'))
@section('content')
<div class="card border-0 shadow-sm overflow-hidden">
    <table class="table mb-0">
        <thead><tr><th>{{ __('panel.title') }}</th><th>{{ __('panel.customer') }}</th><th>{{ __('panel.status') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                <td>{{ $item->title }}</td>
                <td>{{ $item->customer?->name }} <code class="small">{{ $item->customer?->publicCode() }}</code></td>
                <td>{{ \App\Support\UiLabels::status($item->status) }}</td>
                <td><a href="{{ route('vendor.direct-quotes.show', $item) }}" class="btn btn-sm btn-outline-primary">{{ __('panel.detail') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-muted">{{ __('panel.no_results') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
