@extends('layouts.outdoor')
@section('title', 'Temsil ettiğim panolar')
@section('content')
<h1 class="h5">Temsil ettiğim yayınlanmış panolar</h1>
<p class="small text-muted">Ajans kopya ilan açmaz. Teklif, bağlı olduğunuz sahiplerin panoları için size gelir.</p>
@if($items->isEmpty())
    <div class="card p-4 text-muted mt-3">Bağlı yayınlanmış pano yok. Sahip davetini onaylayın.</div>
@else
    <div class="row g-3 mt-1">
        @foreach($items as $inv)
            <div class="col-md-4">
                <div class="card p-3 h-100">
                    <strong>{{ $inv->title }}</strong>
                    <div class="small text-muted">{{ $inv->vendor?->name }} · {{ $inv->country_code }} {{ $inv->city }}</div>
                    <a class="btn btn-outline-primary btn-sm mt-2" href="{{ route('outdoor.show', $inv->slug) }}">Katalogda aç</a>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-3">{{ $items->links() }}</div>
@endif
@endsection
