@extends('layouts.admin')
@section('title', 'Hakedisler')
@section('content')
<div class="card p-4 mb-4">
    <h2 class="h6 mb-3">Hakedise hazir siparisler</h2>
    @if($grouped->isEmpty())
        <p class="text-muted small mb-0">Bekleyen hakedis yok.</p>
    @else
        @foreach($grouped as $g)
            <div class="border rounded p-3 mb-3">
                <strong>{{ $g['vendor']->name }}</strong>
                <p class="small mb-2">Saticiya odenecek: TL {{ number_format($g['total_vendor_amount'], 2, ',', '.') }} | Komisyon: TL {{ number_format($g['total_commission'], 2, ',', '.') }}</p>
                <form method="POST" action="{{ route('admin.payouts.approve') }}" class="d-inline">
                    @csrf
                    @foreach($g['orders'] as $o)
                        <input type="hidden" name="order_ids[]" value="{{ $o->id }}">
                    @endforeach
                    <button type="submit" class="btn btn-success btn-sm">Onayla</button>
                </form>
            </div>
        @endforeach
    @endif
</div>
<div class="card p-4">
    <h3 class="h6 mb-3">Son onaylanan hakedisler</h3>
    @if($recentlyApproved->isEmpty())
        <p class="text-muted small mb-0">Henuz yok.</p>
    @else
        <table class="table table-sm mb-0">
            <thead><tr><th>Siparis</th><th>Satici</th><th>Tarih</th></tr></thead>
            <tbody>
                @foreach($recentlyApproved as $o)
                    <tr>
                        <td>{{ $o->order_number }}</td>
                        <td>{{ $o->vendor?->name }}</td>
                        <td>{{ $o->payout_at?->format('d.m.Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
