@extends('layouts.vendor')
@section('title', __('panel.my_categories'))
@section('content')
<form method="get" class="mb-3">
    <select name="channel" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="physical_quote" @selected($channel==='physical_quote')>{{ __('panel.channel_physical') }}</option>
        <option value="freelancer" @selected($channel==='freelancer')>{{ __('panel.channel_freelancer') }}</option>
        <option value="tabela" @selected($channel==='tabela')>{{ __('panel.channel_tabela') }}</option>
    </select>
</form>

@if($pending->isNotEmpty())
<div class="alert alert-warning small">{{ __('panel.pending_category_requests', ['count' => $pending->count()]) }}</div>
@endif

<form method="post" action="{{ route('vendor.categories.store') }}" class="card p-3">
    @csrf
    <input type="hidden" name="channel" value="{{ $channel }}">
    <p class="small text-muted">{{ __('panel.category_request_help') }}</p>
    <p class="small">{{ __('panel.current_categories') }}:
        @forelse($vendor->quoteCategories as $c)
            <span class="badge bg-light text-dark border">{{ $c->name }}</span>
        @empty — @endforelse
    </p>
    @include('partials.multi-search-filter', [
        'name' => 'category_ids[]',
        'options' => $categories,
        'selected' => $currentIds,
        'placeholder' => __('panel.search_category'),
    ])
    <button class="btn btn-primary btn-sm mt-3">{{ __('panel.submit_for_approval') }}</button>
</form>
@endsection
