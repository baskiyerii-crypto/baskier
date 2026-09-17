@extends('layouts.app')
@section('title', 'Açık hava mecraları')
@section('content')
<div class="by-container py-10">
    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Açık hava</p>
    <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Mecra kataloğu</h1>
    <p class="mt-2 text-sm text-slate-600">Panoları il / ilçe gez. Fiyat tahmindir; plan talebi satıcı başına teklife dönüşür.</p>

    @if(session('success'))<div class="alert alert-success mt-4">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger mt-4">{{ session('error') }}</div>@endif

    <form class="mt-6 by-card p-4 grid gap-3 md:grid-cols-5" method="GET">
        <select name="il" class="by-input" onchange="this.form.requestSubmit()">
            <option value="">Tüm iller</option>
            @foreach($provinces as $p)
                <option value="{{ $p->id }}" @selected((int)$provinceId === (int)$p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
        <select name="ilce" class="by-input" @disabled(!$provinceId)>
            <option value="">Tüm ilçeler</option>
            @foreach($districts as $d)
                <option value="{{ $d->id }}" @selected((int)$districtId === (int)$d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
        <select name="kategori" class="by-input">
            <option value="">Tüm kategoriler</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected((int)$categoryId === (int)$c->id)>{{ $c->localizedName() }}</option>
            @endforeach
        </select>
        <button class="by-btn-primary">Filtrele</button>
        <a href="{{ route('outdoor.index') }}" class="by-btn-secondary text-center">Sıfırla</a>
    </form>

    @if(count($basket))
        <div class="mt-4 by-card p-4">
            <div class="flex items-center justify-between gap-3">
                <p class="font-semibold">Plan sepeti ({{ count($basket) }})</p>
                @auth
                    <form method="POST" action="{{ auth()->user()->isVendor() ? route('vendor.outdoor.plans.store') : route('customer.outdoor.plans.store') }}">
                        @csrf
                        <button class="by-btn-cta">Plan talebi gönder</button>
                    </form>
                @else
                    <a class="by-btn-cta" href="{{ route('login') }}">Giriş yapıp gönder</a>
                @endauth
            </div>
            <ul class="mt-3 text-sm text-slate-600 space-y-1">
                @foreach($basket as $i => $line)
                    <li class="flex justify-between gap-2">
                        <span>{{ $line['title'] ?? ('Pano #'.$line['inventory_id']) }} · {{ $line['starts_on'] }} – {{ $line['ends_on'] }}</span>
                        <form method="POST" action="{{ route('outdoor.basket.remove') }}">@csrf<input type="hidden" name="index" value="{{ $i }}"><button class="text-rose-600 text-xs">Kaldır</button></form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($items->isEmpty())
        <div class="mt-8 by-card p-8 text-center text-slate-600">Bu filtrede yayınlanmış pano yok.</div>
    @else
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($items as $inv)
                <a href="{{ route('outdoor.show', $inv->slug) }}" class="by-card by-card-hover overflow-hidden">
                    <div class="aspect-4/3 bg-slate-100">
                        @if($inv->coverPath())
                            <img src="{{ \App\Support\MediaUrl::public($inv->coverPath()) }}" alt="" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-slate-500">{{ $inv->city ?: $inv->province?->name }} {{ $inv->district ?: $inv->districtRel?->name }}</p>
                        <h2 class="mt-1 font-bold text-slate-900">{{ $inv->title }}</h2>
                        <p class="mt-1 text-sm text-slate-600">{{ $inv->vendor?->name }}</p>
                        @if($inv->list_price)
                            <p class="mt-2 text-sm font-semibold">Tahmini ₺{{ number_format($inv->list_price, 2, ',', '.') }} / {{ $inv->price_unit }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $items->links() }}</div>
    @endif
</div>
@endsection
