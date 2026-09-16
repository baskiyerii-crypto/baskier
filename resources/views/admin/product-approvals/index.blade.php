@extends('layouts.admin')
@section('title', __('panel.nav_product_approvals'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">{{ __('panel.nav_product_approvals') }}</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($groups->isEmpty())
    <div class="card p-4 text-muted">Onay bekleyen ürün yok.</div>
@else
    @foreach($groups as $vendorId => $items)
        @php $vendor = $items->first()->vendor; @endphp
        <details class="card mb-3" open>
            <summary class="p-3 fw-semibold d-flex justify-content-between align-items-center" style="cursor:pointer;list-style:none;">
                <span>{{ $vendor?->name ?? ('Satıcı #'.$vendorId) }}</span>
                <span class="badge bg-warning text-dark">{{ $items->count() }} bekliyor</span>
            </summary>
            <div class="px-3 pb-3">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Kategori</th>
                                <th>Fiyat</th>
                                <th>Gönderim</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $product)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $product->name }}</div>
                                        <div class="small text-muted">#{{ $product->id }}</div>
                                    </td>
                                    <td>{{ $product->category?->name ?? '-' }}</td>
                                    <td>₺{{ number_format((float) $product->price, 2, ',', '.') }}</td>
                                    <td class="small">{{ optional($product->submitted_for_moderation_at ?? $product->updated_at)->format('d.m.Y H:i') }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.product-approvals.approve', $product) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success" type="submit">Onayla</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.product-approvals.reject', $product) }}" class="d-inline-flex gap-1 mt-1 mt-md-0">
                                            @csrf
                                            <input type="text" name="moderation_note" class="form-control form-control-sm" placeholder="Red notu" style="width:140px">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Reddet</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    @endforeach
@endif
@endsection
