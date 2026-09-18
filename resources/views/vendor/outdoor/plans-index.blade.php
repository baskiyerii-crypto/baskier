@extends('layouts.outdoor')
@section('title', 'Kampanya planlarım')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h5 mb-0">Havuz planlarım</h1>
    <form method="POST" action="{{ route('outdoor-panel.plans.store') }}">@csrf<button class="btn btn-primary btn-sm">Sepetteki planı gönder</button></form>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($plans->isEmpty())
    <div class="card p-4 text-muted">Plan yok.</div>
@else
    @foreach($plans as $plan)
        <a class="card p-3 mb-2 d-block text-decoration-none" href="{{ route('outdoor-panel.plans.show', $plan) }}">
            {{ $plan->title }} · {{ $plan->status }}
        </a>
    @endforeach
    {{ $plans->links() }}
@endif
@endsection
