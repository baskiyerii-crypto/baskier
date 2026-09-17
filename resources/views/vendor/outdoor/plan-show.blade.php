@extends('layouts.vendor')
@section('title', 'Kampanya planı')
@section('content')
<h1 class="h5">{{ $plan->title }}</h1>
<p class="small">{{ $plan->status }}</p>
@foreach($plan->vendorRequests as $req)
    <div class="card p-3 mb-2">
        <strong>{{ $req->vendor?->name }}</strong> · {{ $req->status }}
        @if($req->latestQuote)<div>Teklif ₺{{ number_format($req->latestQuote->amount,2,',','.') }}</div>@endif
    </div>
@endforeach
@endsection
