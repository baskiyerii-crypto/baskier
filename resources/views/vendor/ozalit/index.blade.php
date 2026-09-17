@extends('layouts.vendor')
@section('title', 'Ozalit teklifleri')
@section('content')
<div class="card p-4 mb-3">
    <p class="small text-muted mb-0">Ozalit ve kağıt çıktı taleplerini inceleyin, teklif verin. Bu kanal diğer teklif modüllerinden ayrıdır.</p>
</div>
<div class="card table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Talep</th><th>Kategori</th><th>Müşteri</th><th>Teklifim</th><th></th></tr></thead>
        <tbody>
        @forelse($requests as $qr)
            <tr>
                <td>{{ $qr->title }}</td>
                <td>{{ $qr->category?->name }}</td>
                <td>{{ $qr->user?->publicCode() }}</td>
                <td>{{ $qr->quotes->first()?->amount ? '₺'.number_format($qr->quotes->first()->amount, 2, ',', '.') : '—' }}</td>
                <td><a href="{{ route('vendor.quote-requests.show', $qr) }}" class="btn btn-sm btn-outline-primary">Talebi incele</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">Açık ozalit talebi yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
