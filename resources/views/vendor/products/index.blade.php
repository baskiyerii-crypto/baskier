@extends('layouts.vendor')

@section('title', 'Ürünlerim - Satıcı Paneli')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('vendor.products.template') }}" class="btn btn-secondary text-xs">Excel Şablonu İndir</a>
        <form action="{{ route('vendor.products.import') }}" method="post" enctype="multipart/form-data" class="flex gap-2 items-center">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control text-xs max-w-[200px]" required>
            <button type="submit" class="btn btn-secondary text-xs">Excel Yükle</button>
        </form>
    </div>
    <a href="{{ route('vendor.products.create') }}" class="btn btn-cta text-xs font-bold">
        + Yeni Ürün Ekle
    </a>
</div>

<div class="by-card p-4 bg-surface border border-border mb-6">
    <form method="GET" action="{{ route('vendor.products.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
        <div class="sm:col-span-6">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control text-xs" placeholder="Ürün adı veya açıklama ile ara...">
        </div>
        <div class="sm:col-span-3">
            <select name="status" class="form-control text-xs">
                <option value="">Tüm Durumlar</option>
                <option value="active" @selected(request('status') === 'active')>Yalnızca Aktif</option>
                <option value="passive" @selected(request('status') === 'passive')>Yalnızca Pasif</option>
            </select>
        </div>
        <div class="sm:col-span-3 flex gap-2">
            <button type="submit" class="btn btn-secondary text-xs flex-1">Filtrele</button>
            @if(request()->hasAny(['q', 'status']))
                <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary text-xs">Temizle</a>
            @endif
        </div>
    </form>
</div>

<div class="by-card bg-surface border border-border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                    <th class="px-5 py-3 w-16">Görsel</th>
                    <th class="px-5 py-3">Ürün Adı</th>
                    <th class="px-5 py-3">Kategori</th>
                    <th class="px-5 py-3">Fiyat</th>
                    <th class="px-5 py-3">Stok</th>
                    <th class="px-5 py-3">Tip</th>
                    <th class="px-5 py-3">Durum</th>
                    <th class="px-5 py-3 text-right">İşlemler</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($products as $p)
                    <tr class="hover:bg-canvas/30 transition-colors">
                        <td class="px-5 py-3">
                            @if($p->main_image)
                                <img src="{{ asset('storage/'.$p->main_image) }}" alt="" class="rounded-lg border border-border w-10 h-10 object-cover">
                            @else
                                <div class="rounded-lg border border-border bg-canvas text-muted flex items-center justify-center w-10 h-10 text-xs">
                                    📷
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-semibold text-xs text-ink">{{ Str::limit($p->name, 45) }}</div>
                            @if($p->variants && $p->variants->isNotEmpty())
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] bg-canvas text-muted border border-border mt-0.5">
                                    {{ $p->variants->count() }} varyant
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-muted">{{ $p->category?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs font-bold text-ink">₺{{ number_format($p->price, 2, ',', '.') }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $p->stock > 0 ? 'bg-canvas text-ink border border-border' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                {{ $p->stock }} adet
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-canvas text-muted border border-border">
                                {{ ($p->product_type ?? 'physical') === 'digital' ? 'Dijital' : 'Fiziksel' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $mod = $p->moderation_status ?? 'approved';
                            @endphp
                            @if($mod === 'pending')
                                <x-badge variant="warning">Onay Bekliyor</x-badge>
                            @elseif($mod === 'rejected')
                                <x-badge variant="danger">Reddedildi</x-badge>
                                @if($p->moderation_note)
                                    <div class="text-[10px] text-muted mt-0.5">{{ $p->moderation_note }}</div>
                                @endif
                            @else
                                <x-badge :variant="$p->is_active ? 'success' : 'neutral'">
                                    {{ $p->is_active ? 'Aktif' : 'Pasif' }}
                                </x-badge>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('products.show', $p->slug) }}" class="btn btn-secondary text-xs py-1 px-2.5" target="_blank" title="Vitrin">Vitrin ↗</a>
                                <a href="{{ route('vendor.products.edit', $p) }}" class="btn btn-secondary text-xs py-1 px-2.5">Düzenle</a>
                                <form action="{{ route('vendor.products.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-secondary text-xs py-1 px-2.5 text-red-600 hover:bg-red-50">Sil</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted text-xs py-8">
                            Henüz ürün bulunmuyor. <a href="{{ route('vendor.products.create') }}" class="font-bold text-cta hover:underline">İlk ürünü hemen ekleyin</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
