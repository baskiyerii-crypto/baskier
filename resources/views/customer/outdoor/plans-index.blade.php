@extends('layouts.account')
@section('title', 'Planlarım')
@section('content')
<h1 class="text-2xl font-extrabold">Açık hava planlarım</h1>
@if(session('success'))<div class="alert alert-success mt-3">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger mt-3">{{ session('error') }}</div>@endif
@if($plans->isEmpty())
    <div class="mt-6 by-card p-8 text-center text-slate-600">Henüz plan yok. <a class="by-link" href="{{ route('outdoor.index') }}">Kataloga git</a></div>
@else
    <div class="mt-6 space-y-3">
        @foreach($plans as $plan)
            <a href="{{ route('customer.outdoor.plans.show', $plan) }}" class="by-card by-card-hover p-4 block">
                <div class="flex justify-between">
                    <strong>{{ $plan->title }}</strong>
                    <span class="text-xs">{{ $plan->status }}</span>
                </div>
                <p class="text-sm text-slate-600 mt-1">{{ $plan->items->count() }} pano · {{ $plan->vendorRequests->count() }} satıcı</p>
            </a>
        @endforeach
    </div>
    <div class="mt-4">{{ $plans->links() }}</div>
@endif
@endsection
