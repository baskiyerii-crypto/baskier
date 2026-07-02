@extends('layouts.vendor')
@section('title', 'Teklif Talepleri')
@section('content')
<div class="card p-4">
    <h2 class="h6 mb-3">Acik teklif talepleri</h2>
    @if($quoteRequests->isEmpty())
        <p class="text-muted small mb-0">Size uygun acik talep yok.</p>
    @else
        <table class="table table-sm mb-0">
            <thead><tr><th>Talep</th><th>Kategori</th><th>Bolge</th><th></th></tr></thead>
            <tbody>
                @foreach($quoteRequests as $qr)
                    <tr>
                        <td>{{ Str::limit($qr->title, 40) }}</td>
                        <td>{{ $qr->category?->name }}</td>
                        <td>{{ $qr->city ?? '-' }}</td>
                        <td>
                            @if($myQuotes->contains($qr->id))
                                <span class="badge bg-secondary">Teklif verdiniz</span>
                            @else
                                <a href="{{ route('vendor.quote-requests.show', $qr) }}" class="btn btn-success btn-sm">Goruntule</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">{{ $quoteRequests->links() }}</div>
    @endif
</div>
@endsection
