@extends('layouts.outdoor')
@section('title', 'Açık hava envanteri')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Envanter</h1>
    @if($canMutate ?? ($role === 'owner'))
        <a href="{{ route('outdoor-panel.inventories.create') }}" class="btn btn-primary btn-sm">Yeni pano</a>
    @endif
</div>
@if($items->isEmpty())
    <div class="card p-4 text-muted">Henüz pano yok.</div>
@else
<div class="card table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Pano</th><th>Durum</th><th>Fiyat</th><th></th></tr></thead>
        <tbody>
        @foreach($items as $inv)
            <tr>
                <td>{{ $inv->title }}<div class="small text-muted">{{ $inv->city }} {{ $inv->district }}</div></td>
                <td>{{ $inv->status }}</td>
                <td>{{ $inv->list_price ? '₺'.number_format($inv->list_price,2,',','.') : '—' }}</td>
                <td class="text-end">
                    @if($canMutate ?? ($role === 'owner'))
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('outdoor-panel.inventories.edit', $inv) }}">Düzenle</a>
                        <form method="POST" action="{{ route('outdoor-panel.inventories.destroy', $inv) }}" class="d-inline" onsubmit="return confirm('Bu pano silinsin mi?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">Sil</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endif
@endsection
