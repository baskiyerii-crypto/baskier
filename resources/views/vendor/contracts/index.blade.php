@extends('layouts.vendor')
@section('title', __('panel.contracts'))
@section('content')
@if($vendor->contract_suspended_at)
    <div class="alert alert-danger">{{ __('panel.contract_suspended_notice') }}</div>
@endif
<div class="card p-3 mb-4">
    <h2 class="h6">{{ __('panel.pending_contracts') }}</h2>
    @forelse($pending as $row)
        <div class="border rounded p-3 mb-2">
            <div class="fw-semibold">{{ $row->contract?->title }} <span class="text-muted small">v{{ $row->version }}</span></div>
            <div class="small text-muted mb-2">{{ __('panel.due_at') }}: {{ optional($row->due_at)->format('d.m.Y H:i') }}</div>
            <div class="small mb-2">{!! \Illuminate\Support\Str::limit(strip_tags($row->contract?->content_html ?? ''), 280) !!}</div>
            <form method="post" action="{{ route('vendor.contracts.accept', $row) }}">
                @csrf
                <button class="btn btn-sm btn-success">{{ __('panel.accept_contract') }}</button>
            </form>
        </div>
    @empty
        <p class="text-muted small mb-0">{{ __('panel.no_pending_contracts') }}</p>
    @endforelse
</div>
<div class="card p-3">
    <h2 class="h6">{{ __('panel.accepted_contracts') }}</h2>
    <ul class="list-unstyled mb-0">
        @foreach($accepted as $row)
            <li class="small py-1 border-bottom">{{ $row->contract?->title }} — {{ optional($row->accepted_at)->format('d.m.Y') }}</li>
        @endforeach
    </ul>
    <div class="mt-2">{{ $accepted->links() }}</div>
</div>
@endsection
