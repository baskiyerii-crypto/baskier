@extends('layouts.vendor')
@section('title', 'Açık hava talepleri')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<h1 class="h5 mb-3">Gelen plan talepleri</h1>
@if($requests->isEmpty())
    <div class="card p-4 text-muted">Talep yok.</div>
@else
    <div class="list-group">
        @foreach($requests as $r)
            <a class="list-group-item list-group-item-action" href="{{ route('vendor.outdoor.requests.show', $r) }}">
                <strong>{{ $r->plan?->title }}</strong>
                <span class="small text-muted">{{ $r->status }} · {{ $r->plan?->planner?->name }}</span>
            </a>
        @endforeach
    </div>
    <div class="mt-3">{{ $requests->links() }}</div>
@endif
@endsection
