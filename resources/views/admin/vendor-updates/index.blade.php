@extends('layouts.admin')
@section('title', __('panel.vendor_updates'))
@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-transparent fw-semibold">{{ __('panel.pending_documents') }}</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>{{ __('panel.vendor') }}</th><th>{{ __('panel.doc_type') }}</th><th></th></tr></thead>
                    <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td>
                                {{ $doc->vendor?->name }}
                                <div><code class="small">{{ $doc->vendor?->user?->publicCode() }}</code></div>
                            </td>
                            <td>{{ \App\Support\UiLabels::documentType($doc->document_type) }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ asset('storage/'.$doc->path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">{{ __('panel.view') }}</a>
                                <form method="post" action="{{ route('admin.vendor-updates.documents.approve', $doc) }}" class="d-inline">@csrf
                                    <select name="freelancer_tier" class="form-select form-select-sm d-inline-block w-auto">
                                        <option value="">{{ __('panel.tier_auto') }}</option>
                                        <option value="standard">{{ __('panel.tier_standard') }}</option>
                                        <option value="medium">{{ __('panel.tier_medium') }}</option>
                                        <option value="professional">{{ __('panel.tier_professional') }}</option>
                                    </select>
                                    <button class="btn btn-sm btn-success">{{ __('panel.approve') }}</button>
                                </form>
                                <form method="post" action="{{ route('admin.vendor-updates.documents.reject', $doc) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-outline-danger">{{ __('panel.reject') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">{{ __('panel.no_results') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-2">{{ $documents->links() }}</div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-transparent fw-semibold">{{ __('panel.pending_profile_changes') }}</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>{{ __('panel.vendor') }}</th><th>{{ __('panel.changes') }}</th><th></th></tr></thead>
                    <tbody>
                    @forelse($profileRequests as $row)
                        <tr>
                            <td>{{ $row->vendor?->name }}</td>
                            <td class="small"><code>{{ json_encode($row->payload, JSON_UNESCAPED_UNICODE) }}</code></td>
                            <td class="text-end text-nowrap">
                                <form method="post" action="{{ route('admin.vendor-updates.profiles.approve', $row) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-success">{{ __('panel.approve') }}</button>
                                </form>
                                <form method="post" action="{{ route('admin.vendor-updates.profiles.reject', $row) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-outline-danger">{{ __('panel.reject') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">{{ __('panel.no_results') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-2">{{ $profileRequests->links() }}</div>
        </div>
    </div>
</div>
@endsection
