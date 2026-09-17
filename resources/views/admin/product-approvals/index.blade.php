@extends('layouts.admin')
@section('title', __('panel.nav_product_approvals') . ' - Yönetim Paneli')
@section('content')
<div class="mb-6">
    <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">{{ __('panel.nav_product_approvals') }}</h1>
    <p class="text-xs text-muted mt-0.5">Satıcılar tarafından vitrine eklenen yeni ve güncellenen ürünlerin onay kuyruğu</p>
</div>

@if(session('success'))
    <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
@endif

@if($groups->isEmpty())
    <div class="by-card p-10 text-center bg-surface border border-border text-xs text-muted">
        Şu anda onay bekleyen ürün bulunmuyor.
    </div>
@else
    <div class="space-y-4">
        @foreach($groups as $vendorId => $items)
            @php $vendor = $items->first()->vendor; @endphp
            <div class="by-card bg-surface border border-border overflow-hidden">
                <div class="p-4 bg-canvas/60 border-b border-border flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm text-ink">{{ $vendor?->name ?? ('Satıcı #'.$vendorId) }}</span>
                        <x-badge variant="warning">{{ $items->count() }} bekliyor</x-badge>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-border bg-canvas/30 text-xs font-semibold uppercase tracking-wider text-muted">
                                <th class="px-5 py-3">Ürün</th>
                                <th class="px-5 py-3">Kategori</th>
                                <th class="px-5 py-3">Fiyat</th>
                                <th class="px-5 py-3">Gönderim Tarihi</th>
                                <th class="px-5 py-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($items as $product)
                                <tr class="hover:bg-canvas/20 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-xs text-ink">{{ $product->name }}</div>
                                        <div class="text-[10px] text-muted">ID: #{{ $product->id }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-muted">{{ $product->category?->name ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-xs font-bold text-ink">₺{{ number_format((float) $product->price, 2, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 text-xs text-muted">{{ optional($product->submitted_for_moderation_at ?? $product->updated_at)->format('d.m.Y H:i') }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                            <form method="POST" action="{{ route('admin.product-approvals.approve', $product) }}" class="inline">
                                                @csrf
                                                <button class="btn btn-secondary text-xs py-1 px-3 text-emerald-700 hover:bg-emerald-50" type="submit">Onayla</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.product-approvals.reject', $product) }}" class="inline-flex gap-1">
                                                @csrf
                                                <input type="text" name="moderation_note" class="form-control text-xs py-1 px-2 w-32" placeholder="Red notu">
                                                <button class="btn btn-secondary text-xs py-1 px-2.5 text-red-600 hover:bg-red-50" type="submit">Reddet</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
