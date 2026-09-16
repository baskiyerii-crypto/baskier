@extends('layouts.admin')
@section('title', __('panel.customers'))
@section('content')
<form class="d-flex gap-2 mb-3" method="get">
    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="BY- / email / {{ __('panel.name') }}">
    <button class="btn btn-sm btn-outline-primary">{{ __('panel.filter') }}</button>
</form>
<div class="card border-0 shadow-sm overflow-hidden">
    <table class="table mb-0">
        <thead><tr><th>ID</th><th>{{ __('panel.name') }}</th><th>{{ __('panel.email') }}</th><th>{{ __('panel.status') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($customers as $c)
            <tr>
                <td><code>{{ $c->publicCode() }}</code></td>
                <td>{{ $c->name }}</td>
                <td>{{ $c->email }}</td>
                <td>{{ $c->is_active ? __('panel.active') : __('panel.passive') }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.customers.show', $c) }}" class="btn btn-sm btn-outline-primary">{{ __('panel.detail') }}</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">{{ __('panel.no_results') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $customers->links() }}</div>
@endsection
