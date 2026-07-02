@extends('layouts.app')
@section('title', 'Teklif Taleplerim')
@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Teklif taleplerim</h1>
        <a href="{{ route('quote-requests.create') }}" class="btn btn-warning">Yeni talep olustur</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($requests->isEmpty())
        <p class="text-muted">Henuz teklif talebiniz yok.</p>
    @else
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <table class="table table-hover mb-0">
                <thead><tr><th>Baslik</th><th>Kategori</th><th>Durum</th><th>Tarih</th><th></th></tr></thead>
                <tbody>
                    @foreach($requests as $r)
                        <tr>
                            <td>{{ Str::limit($r->title, 40) }}</td>
                            <td>{{ $r->category?->name }}</td>
                            <td>{{ $r->status === 'open' ? 'Acik' : 'Kapali' }}</td>
                            <td>{{ $r->created_at->format('d.m.Y') }}</td>
                            <td><a href="{{ route('quote-requests.show', $r) }}" class="btn btn-outline-secondary btn-sm">Goruntule</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
