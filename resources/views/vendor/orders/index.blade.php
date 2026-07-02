@extends('layouts.vendor')
@section('title', 'Siparisler')
@section('content')
<div class="card overflow-hidden">
    <table class="table table-hover mb-0">
        <thead><tr><th>Siparis no</th><th>Musteri</th><th>Tutar</th><th>Durum</th><th></th></tr></thead>
        <tbody>
            @forelse($orders as $o)
                <tr>
                    <td>{{ $o->order_number }}</td>
                    <td>{{ $o->user?->name }}</td>
                    <td>TL {{ number_format($o->subtotal, 2, ',', '.') }}</td>
                    <td>{{ $o->status }}</td>
                    <td><a href="{{ route('vendor.orders.show', $o) }}" class="btn btn-outline-secondary btn-sm">Detay</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">Siparis yok.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
