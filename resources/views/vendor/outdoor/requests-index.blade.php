@extends('layouts.outdoor')
@section('title', 'Açık hava talepleri')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Gelen plan talepleri</h1>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('outdoor-panel.requests.export', array_filter(['status' => $status ?? null])) }}">Excel indir</a>
</div>
<form method="GET" class="d-flex gap-2 mb-3">
    <select name="status" class="form-select form-select-sm" style="max-width:12rem">
        <option value="">Tüm durumlar</option>
        @foreach(['pending' => 'Bekleyen', 'quoted' => 'Teklifli', 'accepted' => 'Kabul'] as $value => $label)
            <option value="{{ $value }}" @selected(($status ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn btn-sm btn-outline-primary">Filtrele</button>
</form>
@if($requests->isEmpty())
    <div class="card p-4 text-muted">Talep yok.</div>
@else
    <div class="list-group">
        @foreach($requests as $r)
            <a class="list-group-item list-group-item-action" href="{{ route('outdoor-panel.requests.show', $r) }}">
                <strong>{{ $r->plan?->title }}</strong>
                <span class="small text-muted">{{ $r->status }} · {{ $r->plan?->planner?->name }}</span>
            </a>
        @endforeach
    </div>
    <div class="mt-3">{{ $requests->links() }}</div>
@endif
@endsection
