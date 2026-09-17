@extends('layouts.vendor')
@section('title', 'Ürün soruları')
@section('content')
<div class="card table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Ürün</th><th>Müşteri</th><th>Soru</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @forelse($questions as $q)
            <tr>
                <td>{{ $q->product?->name }}</td>
                <td>{{ $q->customer?->publicCode() }}</td>
                <td class="small">{{ \Illuminate\Support\Str::limit($q->question, 80) }}</td>
                <td>{{ $q->status === 'answered' ? 'Yanıtlandı' : 'Açık' }}</td>
                <td><a href="{{ route('vendor.product-questions.show', $q) }}" class="btn btn-sm btn-outline-primary">Aç</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">Ürün sorusu yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $questions->links() }}</div>
@endsection
