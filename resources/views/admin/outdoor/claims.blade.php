@extends('layouts.admin')
@section('title', 'Outdoor çift ilan')
@section('content')
<div class="card table-responsive">
<table class="table mb-0">
    <thead><tr><th>Pano</th><th>Raporlayan</th><th>Durum</th><th></th></tr></thead>
    <tbody>
    @forelse($claims as $c)
        <tr>
            <td>{{ $c->inventory?->title }}<div class="small">{{ $c->evidence }}</div></td>
            <td>{{ $c->reporterVendor?->name }}</td>
            <td>{{ $c->status }}</td>
            <td>
                @if($c->status==='pending')
                    <form method="POST" action="{{ route('admin.outdoor.claims.resolve', $c) }}" class="d-inline">@csrf<input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-success">Onayla (ilanı düşür)</button></form>
                    <form method="POST" action="{{ route('admin.outdoor.claims.resolve', $c) }}" class="d-inline">@csrf<input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-outline-secondary">Reddet</button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="text-muted">Rapor yok.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $claims->links() }}
@endsection
