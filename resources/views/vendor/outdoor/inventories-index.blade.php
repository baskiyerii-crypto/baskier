@extends('layouts.outdoor')
@section('title', 'Açık hava envanteri')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Envanter</h1>
    @if($canMutate ?? ($role === 'owner'))
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('outdoor-panel.inventories.export') }}" class="btn btn-outline-secondary btn-sm">Excel indir</a>
            <a href="{{ route('outdoor-panel.inventories.template') }}" class="btn btn-outline-secondary btn-sm">Şablon</a>
            <a href="{{ route('outdoor-panel.inventories.create') }}" class="btn btn-primary btn-sm">Yeni pano</a>
        </div>
    @endif
</div>
@if($canMutate ?? ($role === 'owner'))
    <form method="POST" action="{{ route('outdoor-panel.inventories.import') }}" enctype="multipart/form-data" class="card p-3 mb-3">
        @csrf
        <p class="small text-muted mb-2">Toplu yükleme: Excel (id boş = yeni) + isteğe bağlı görsel ZIP. <code>image_files</code> doluysa o panonun görselleri değişir.</p>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control form-control-sm" style="max-width:220px" required>
            <input type="file" name="images_zip" accept=".zip" class="form-control form-control-sm" style="max-width:220px">
            <button class="btn btn-outline-primary btn-sm">Yükle</button>
        </div>
    </form>
@endif
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
