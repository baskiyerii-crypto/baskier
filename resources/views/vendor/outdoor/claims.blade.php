@extends('layouts.vendor')
@section('title', 'Çift ilan raporlarım')
@section('content')
<h1 class="h5">Raporlarım</h1>
@if($claims->isEmpty())
    <div class="card p-4 text-muted">Rapor yok. Havuzdan bildirim açabilirsiniz.</div>
@else
    <table class="table">
        <thead><tr><th>Pano</th><th>Durum</th></tr></thead>
        <tbody>
        @foreach($claims as $c)
            <tr><td>{{ $c->inventory?->title }}</td><td>{{ $c->status }}</td></tr>
        @endforeach
        </tbody>
    </table>
    {{ $claims->links() }}
@endif
@endsection
