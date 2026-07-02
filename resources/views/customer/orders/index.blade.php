@extends('layouts.account')

@section('title', 'Siparişlerim')

@section('content')
    <h1 class="h5 mb-4">Siparişlerim</h1>
    <div class="rounded-3 border overflow-hidden">
        <table class="table table-hover mb-0 small">
            <thead><tr><th>No</th><th>Tür</th><th>Satıcı / iş</th><th>Tutar</th><th>Durum</th><th></th></tr></thead>
            <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td>#{{ $o->order_number }}</td>
                        <td>{{ $o->type }}</td>
                        <td>{{ $o->vendor?->name ?? '—' }}</td>
                        <td>₺{{ number_format($o->subtotal, 2, ',', '.') }}</td>
                        <td><span class="badge bg-light text-dark">{{ $o->status }}</span></td>
                        <td class="text-end"><a href="{{ route('account.orders.show', $o) }}" class="btn btn-sm btn-outline-dark">Detay</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted p-4">Henüz sipariş yok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
@endsection
