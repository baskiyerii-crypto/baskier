@extends('layouts.vendor')
@section('title', 'Havuz')
@section('content')
<h1 class="h5">Diğer sahiplerin yayınlanmış panoları</h1>
<p class="small text-muted">Boş tarihleri katalogdan plan sepetine ekleyip kampanya talebi oluşturabilirsiniz. Teklif yine asıl sahibe gider.</p>
@if($items->isEmpty())
    <div class="card p-4 text-muted mt-3">Havuzda pano yok.</div>
@else
    <div class="row g-3 mt-1">
        @foreach($items as $inv)
            <div class="col-md-4">
                <div class="card p-3 h-100">
                    <strong>{{ $inv->title }}</strong>
                    <div class="small text-muted">{{ $inv->vendor?->name }} · {{ $inv->city }}</div>
                    <a class="btn btn-outline-primary btn-sm mt-2" href="{{ route('outdoor.show', $inv->slug) }}">Katalogda aç</a>
                    <form method="POST" action="{{ route('vendor.outdoor.claims.store') }}" class="mt-2">
                        @csrf
                        <input type="hidden" name="ooh_inventory_id" value="{{ $inv->id }}">
                        <input name="evidence" class="form-control form-control-sm mb-1" placeholder="Bu envanter benim, çünkü...">
                        <button class="btn btn-outline-danger btn-sm">Çift ilan bildir</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-3">{{ $items->links() }}</div>
@endif
@endsection
