@extends('layouts.app')

@section('title', 'Satıcılar - BaskıYeri Pazaryeri')

@section('content')
    <div class="by-container py-6">
        <div class="by-card p-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Keşfet</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Satıcılar</h1>
                    <p class="mt-1 text-sm text-slate-600">İş koluna göre filtreleyip satıcı profillerini inceleyin.</p>
                </div>

                @if(isset($businessTypes) && $businessTypes->isNotEmpty())
                    <form method="get" action="{{ route('vendors.index') }}" class="flex items-end gap-2">
                        <div>
                            <label class="text-xs font-semibold text-slate-600">İş kolu</label>
                            <select name="business_type" class="mt-1 w-64 rounded-full border border-slate-200 bg-white/80 px-4 py-2.5 text-sm outline-none ring-orange-400 focus:ring-2" onchange="this.form.submit()">
                                <option value="">Tümü</option>
                                @foreach($businessTypes as $bt)
                                    <option value="{{ $bt->slug }}" @selected(request('business_type') === $bt->slug)>{{ $bt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(request('business_type'))
                            <a class="by-btn-secondary" href="{{ route('vendors.index') }}">Temizle</a>
                        @endif
                    </form>
                @endif
            </div>
        </div>

        <div class="mt-6">
            @if($vendors->isEmpty())
                <div class="by-card p-8 text-center">
                    <p class="text-sm text-slate-600">Satıcı bulunamadı.</p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                    @foreach($vendors as $vendor)
                        <a href="{{ route('vendors.show', $vendor->slug) }}" class="group by-card by-card-hover p-5">
                            <div class="flex items-start gap-3">
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-orange-50 text-lg font-extrabold text-orange-900 border border-orange-200">
                                    {{ mb_substr($vendor->name, 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $vendor->name }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        @if($vendor->rating_average)
                                            <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-800">
                                                ★ {{ number_format($vendor->rating_average, 1) }} <span class="font-normal text-amber-700">({{ $vendor->reviews_count }})</span>
                                            </span>
                                        @endif
                                        @if($vendor->email)
                                            <span class="truncate">{{ $vendor->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <p class="mt-4 text-sm leading-relaxed text-slate-600">
                                {{ \Illuminate\Support\Str::limit($vendor->description, 120) }}
                            </p>

                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500">Profili görüntüle</span>
                                <span class="text-slate-400 transition group-hover:text-slate-700">→</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $vendors->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

