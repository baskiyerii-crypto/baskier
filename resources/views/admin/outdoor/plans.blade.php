@extends('layouts.admin')
@section('title', 'Outdoor planlar')
@section('content')
<div class="card table-responsive">
<table class="table mb-0">
    <thead><tr><th>Plan</th><th>Planlayan</th><th>Durum</th><th>Satıcı sayısı</th></tr></thead>
    <tbody>
    @forelse($plans as $p)
        <tr>
            <td>#{{ $p->id }} {{ $p->title }}</td>
            <td>{{ $p->planner?->name }} ({{ $p->planner_type }})</td>
            <td>{{ $p->status }}</td>
            <td>{{ $p->vendorRequests->count() }}</td>
        </tr>
    @empty
        <tr><td colspan="4" class="text-muted">Plan yok.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $plans->links() }}
@endsection
