@extends('layouts.outdoor')
@section('title', $vendor->isOutdoorAgency() ? 'Bağlı sahipler' : 'Ajanslarım')
@section('content')
<h1 class="h5 mb-3">{{ $vendor->isOutdoorAgency() ? 'Bağlı mecra sahipleri' : 'Ajanslarım' }}</h1>
<p class="small text-muted">Ajans birden fazla sahip ile çalışabilir. Münhasır bağda yalnız o ajans teklif verir. Bağlantı dizinden kurulur; e-posta ile davet yoktur.</p>
<p class="small mb-3"><a href="{{ route('outdoor-panel.directory') }}">Dizinden bağlan</a></p>

@php $rows = $vendor->isOutdoorAgency() ? $asAgency : $asOwner; @endphp
@if($rows->isEmpty())
    <div class="card p-4 text-muted">Henüz bağ yok.</div>
@else
    <div class="table-responsive card">
        <table class="table mb-0">
            <thead><tr><th>Karşı taraf</th><th>Durum</th><th>Münhasır</th><th></th></tr></thead>
            <tbody>
            @foreach($rows as $row)
                @php $other = $vendor->isOutdoorAgency() ? $row->owner : $row->agency; @endphp
                <tr>
                    <td>{{ $other?->name }} <div class="small text-muted">{{ $other?->city }}</div></td>
                    <td>{{ $row->status }}</td>
                    <td>{{ $row->exclusive ? 'Evet' : 'Hayır' }}</td>
                    <td class="text-end">
                        @if($row->isPending() && (int) $row->invited_by_vendor_id !== (int) $vendor->id)
                            <form method="POST" action="{{ route('outdoor-panel.representations.accept', $row) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Onayla</button></form>
                        @endif
                        @if($row->status !== 'revoked')
                            <form method="POST" action="{{ route('outdoor-panel.representations.revoke', $row) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">İptal</button></form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
