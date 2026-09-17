@extends('layouts.vendor')
@section('title', 'Sipariş soruları')
@section('content')
<div class="card table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Sipariş</th><th>Müşteri</th><th>Konu</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @forelse($questions as $q)
            <tr>
                <td>#{{ $q->order?->order_number }}</td>
                <td>{{ $q->customer?->publicCode() }}</td>
                <td>{{ $q->subject }}</td>
                <td>{{ $q->status === 'answered' ? 'Yanıtlandı' : 'Açık' }}</td>
                <td><a href="{{ route('vendor.order-questions.show', $q) }}" class="btn btn-sm btn-outline-primary">Aç</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">Sipariş sorusu yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $questions->links() }}</div>
@endsection
