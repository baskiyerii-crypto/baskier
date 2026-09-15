@extends('layouts.admin')
@section('title', __('panel.category_requests'))
@section('content')
<form class="mb-3 d-flex gap-2">
    <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="">{{ __('panel.all_statuses') }}</option>
        <option value="pending" @selected(request('status')==='pending')>pending</option>
        <option value="approved" @selected(request('status')==='approved')>approved</option>
        <option value="rejected" @selected(request('status')==='rejected')>rejected</option>
    </select>
</form>
<div class="card overflow-hidden">
    <table class="table mb-0">
        <thead><tr><th>ID</th><th>{{ __('panel.vendor') }}</th><th>{{ __('panel.channel') }}</th><th>{{ __('panel.categories') }}</th><th>{{ __('panel.status') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($requests as $row)
            <tr>
                <td>{{ $row->id }}</td>
                <td>{{ $row->vendor?->name }} <code class="small">{{ $row->vendor?->user?->publicCode() }}</code></td>
                <td>{{ \App\Support\UiLabels::channel($row->channel) }}</td>
                <td class="small">{{ implode(', ', $row->category_ids ?? []) }}</td>
                <td>{{ \App\Support\UiLabels::status($row->status) }}</td>
                <td class="text-end">
                    @if($row->status === 'pending')
                        <form method="post" action="{{ route('admin.vendor-category-requests.approve', $row) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-success">{{ __('panel.approve') }}</button>
                        </form>
                        <form method="post" action="{{ route('admin.vendor-category-requests.reject', $row) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-danger">{{ __('panel.reject') }}</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted">{{ __('panel.no_results') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
