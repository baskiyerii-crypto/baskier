@extends('layouts.admin')
@section('title', 'Outdoor envanter')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <div class="btn-group btn-group-sm">
        @foreach(['pending_review'=>'İnceleme','published'=>'Yayın','rejected'=>'Red','all'=>'Tümü'] as $k=>$lab)
            <a href="{{ route('admin.outdoor.inventories', ['status'=>$k]) }}" class="btn {{ $status===$k ? 'btn-primary' : 'btn-outline-primary' }}">{{ $lab }}</a>
        @endforeach
    </div>
</div>
<div class="card table-responsive">
<table class="table mb-0">
    <thead><tr><th>Pano</th><th>Satıcı</th><th>Durum</th><th>Çakışma</th><th></th></tr></thead>
    <tbody>
    @forelse($items as $inv)
        <tr>
            <td>{{ $inv->title }}<div class="small text-muted">{{ $inv->city }} / ruhsat {{ $inv->permit_no ?: '—' }}</div></td>
            <td>{{ $inv->vendor?->name }}</td>
            <td>{{ $inv->status }}</td>
            <td>
                @if(($collisionCounts[$inv->geo_fingerprint] ?? 0) > 1)
                    <span class="badge text-bg-warning">Aday çift ilan</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td class="text-end">
                @if($inv->status === 'pending_review')
                    <form method="POST" action="{{ route('admin.outdoor.inventories.publish', $inv) }}" class="d-inline">@csrf<button class="btn btn-success btn-sm">Yayınla</button></form>
                    <form method="POST" action="{{ route('admin.outdoor.inventories.reject', $inv) }}" class="d-inline">@csrf<input name="rejection_reason" placeholder="Neden" required class="form-control form-control-sm d-inline-block" style="width:180px"><button class="btn btn-outline-danger btn-sm">Red</button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-muted">Kayıt yok.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
