@extends('layouts.admin')

@section('title', __('panel.vendors'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <form method="get" class="d-flex flex-wrap gap-3 align-items-start">
        <div style="min-width:260px">
            <label class="form-label small mb-1">{{ __('panel.filter_vendors') }}</label>
            @include('partials.multi-search-filter', [
                'name' => 'vendor_ids[]',
                'options' => $allVendors,
                'selected' => request('vendor_ids', []),
                'placeholder' => __('panel.search_vendor'),
            ])
        </div>
        <div>
            <label class="form-label small mb-1">{{ __('panel.search') }}</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="BY- / email">
        </div>
        <div class="align-self-end">
            <button class="btn btn-sm btn-outline-primary">{{ __('panel.filter') }}</button>
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('panel.reset') }}</a>
        </div>
    </form>
    <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary btn-sm">{{ __('panel.new_vendor') }}</a>
</div>
<div class="card overflow-hidden">
        <table class="table table-hover mb-0">
            <thead><tr><th>ID</th><th>{{ __('panel.name') }}</th><th>{{ __('panel.business_types') }}</th><th>{{ __('panel.email') }}</th><th>{{ __('panel.products') }}</th><th>{{ __('panel.risk') }}</th><th>{{ __('panel.modules') }}</th><th>{{ __('panel.status') }}</th><th></th></tr></thead>
            <tbody>
                @forelse($vendors as $v)
                    <tr>
                        <td><code class="small">{{ $v->user?->publicCode() ?? '—' }}</code></td>
                        <td>{{ $v->name }}</td>
                        <td class="small">
                            @forelse($v->businessTypes as $bt)
                                <span class="badge bg-light text-dark border">{{ $bt->name }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>{{ $v->email }}</td>
                        <td>{{ $v->products_count }}</td>
                        <td>
                            @php
                                $riskLabel = match($v->risk_band) {
                                    'safe' => __('panel.risk_safe'), 'medium' => __('panel.risk_medium'), 'risky' => __('panel.risk_risky'), default => '—'
                                };
                                $riskClass = match($v->risk_band) {
                                    'safe' => 'bg-success-subtle text-success', 'medium' => 'bg-warning text-dark', 'risky' => 'bg-danger-subtle text-danger', default => 'bg-light text-muted border'
                                };
                            @endphp
                            <span class="badge {{ $riskClass }}">{{ $riskLabel }}</span>
                        </td>
                        <td class="small">
                            <div>Freelancer: <span class="badge {{ $v->hasActiveFreelancerModule() ? 'bg-success-subtle text-success' : 'bg-light text-muted border' }}">{{ $v->hasActiveFreelancerModule() ? __('panel.active') : __('panel.passive') }}</span></div>
                            <div class="mt-1">{{ __('panel.quotes') }}: <span class="badge {{ $v->hasActiveQuotesModule() ? 'bg-success-subtle text-success' : 'bg-light text-muted border' }}">{{ $v->hasActiveQuotesModule() ? __('panel.active') : __('panel.passive') }}</span></div>
                        </td>
                        <td>{{ $v->is_active ? __('panel.active') : __('panel.passive') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.vendors.show', $v) }}" class="btn btn-outline-primary btn-sm">{{ __('panel.detail') }}</a>
                            <a href="{{ route('admin.vendors.edit', $v) }}" class="btn btn-outline-secondary btn-sm">{{ __('panel.edit') }}</a>
                            <form action="{{ route('admin.vendors.destroy', $v) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('panel.confirm_delete') }}');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('panel.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-muted">{{ __('panel.no_vendors') }}</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
<div class="mt-3">{{ $vendors->links() }}</div>
@endsection
