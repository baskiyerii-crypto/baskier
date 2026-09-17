@extends('layouts.app')
@section('title', $inventory->title)
@section('content')
<div class="by-container py-10">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <a href="{{ route('outdoor.index') }}" class="text-sm text-slate-500">← Katalog</a>
    <div class="mt-4 grid gap-6 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <div class="aspect-4/3 rounded-3xl overflow-hidden bg-slate-100">
                @if($inventory->coverPath())
                    <img src="{{ \App\Support\MediaUrl::public($inventory->coverPath()) }}" alt="" class="h-full w-full object-cover">
                @endif
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($inventory->images as $img)
                    <img src="{{ \App\Support\MediaUrl::public($img->path) }}" class="h-16 w-20 rounded-xl object-cover" alt="">
                @endforeach
            </div>
        </div>
        <div class="lg:col-span-5 by-card p-5">
            <p class="text-xs uppercase tracking-wider text-slate-500">{{ $inventory->category?->name }}</p>
            <h1 class="mt-1 text-2xl font-extrabold">{{ $inventory->title }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ $inventory->city ?: $inventory->province?->name }} / {{ $inventory->district ?: $inventory->districtRel?->name }}</p>
            <p class="mt-2 text-sm">Satıcı: <strong>{{ $inventory->vendor?->name }}</strong> <span class="text-slate-500">(iletişim onay sonrası)</span></p>
            @if($inventory->list_price)
                <p class="mt-3 font-semibold">Tahmini ₺{{ number_format($inventory->list_price, 2, ',', '.') }} / {{ $inventory->price_unit }} · garanti değildir</p>
            @endif
            <p class="mt-3 text-sm text-slate-600">{{ $inventory->description }}</p>
            @php
                $mapLat = (float) $inventory->lat;
                $mapLng = (float) $inventory->lng;
                $delta = 0.008;
                $bbox = ($mapLng - $delta).','.($mapLat - $delta).','.($mapLng + $delta).','.($mapLat + $delta);
            @endphp
            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <iframe
                    title="Pano konumu"
                    class="h-56 w-full"
                    loading="lazy"
                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $bbox }}&amp;layer=mapnik&amp;marker={{ $mapLat }}%2C{{ $mapLng }}"
                ></iframe>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                <a class="underline" href="https://www.openstreetmap.org/?mlat={{ $mapLat }}&amp;mlon={{ $mapLng }}#map=17/{{ $mapLat }}/{{ $mapLng }}" target="_blank" rel="noopener">Haritada aç</a>
            </p>

            <form method="POST" action="{{ route('outdoor.plan.add', $inventory->slug) }}" class="mt-5 space-y-3">
                @csrf
                <label class="text-xs font-semibold">Başlangıç<input type="date" name="starts_on" class="by-input mt-1" required min="{{ now()->toDateString() }}"></label>
                <label class="text-xs font-semibold">Bitiş<input type="date" name="ends_on" class="by-input mt-1" required min="{{ now()->toDateString() }}"></label>
                <button class="by-btn-cta w-full">Plan talebine ekle</button>
            </form>
        </div>
    </div>
    <div class="mt-8 by-card p-5">
        <h2 class="font-bold">Takvim (doluluk)</h2>
        @forelse($calendar as $row)
            <p class="text-sm text-slate-600 mt-1">{{ $row['starts_on'] }} – {{ $row['ends_on'] }} · {{ $row['kind'] }}</p>
        @empty
            <p class="text-sm text-slate-500 mt-2">Şu an blokaj yok.</p>
        @endforelse
    </div>
</div>
@endsection
