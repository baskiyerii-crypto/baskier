@extends('layouts.app')

@section('title', __('ui.vendors_title'))

@section('content')
    <div class="by-container py-6">
        <div class="by-card p-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('ui.discover') }}</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ __('ui.vendors') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ __('ui.vendors_body') }}</p>
                </div>

                @if(isset($businessTypes) && $businessTypes->isNotEmpty())
                    <form method="get" action="{{ route('vendors.index') }}" class="flex items-end gap-2">
                        <div>
                            <label class="text-xs font-semibold text-slate-600">{{ __('ui.business_type') }}</label>
                            <select name="business_type" class="mt-1 w-64 rounded-full border border-slate-200 bg-white/80 px-4 py-2.5 text-sm outline-none ring-orange-400 focus:ring-2" onchange="this.form.submit()">
                                <option value="">{{ __('ui.all') }}</option>
                                @foreach($businessTypes as $bt)
                                    <option value="{{ $bt->slug }}" @selected(request('business_type') === $bt->slug)>{{ $bt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(request('business_type'))
                            <a class="by-btn-secondary" href="{{ route('vendors.index') }}">{{ __('ui.clear') }}</a>
                        @endif
                    </form>
                @endif
            </div>
        </div>

        <div class="mt-6">
            @if($vendors->isEmpty())
                <div class="by-card p-8 text-center">
                    <p class="text-sm text-slate-600">{{ __('ui.no_vendors') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($vendors as $vendor)
                        <a href="{{ route('vendors.show', $vendor->slug) }}" class="group by-card by-card-hover overflow-hidden">
                            <div class="relative h-28 bg-slate-800">
                                @if($vendor->coverUrl())
                                    <img src="{{ $vendor->coverUrl() }}" alt="" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full bg-gradient-to-br from-slate-900 via-slate-800 to-orange-900"></div>
                                @endif
                                <div class="absolute -bottom-6 left-4 h-14 w-14 overflow-hidden rounded-2xl border-2 border-white bg-white shadow">
                                    @if($vendor->logoUrl())
                                        <img src="{{ $vendor->logoUrl() }}" alt="{{ $vendor->name }}" class="h-full w-full object-contain p-1">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-orange-50 text-lg font-extrabold text-orange-900">{{ mb_substr($vendor->name, 0, 1) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="p-5 pt-8">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $vendor->name }}</p>
                                    <x-trust-badge :vendor="$vendor" size="sm" />
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    @if($vendor->rating_average)
                                        ★ {{ number_format($vendor->rating_average, 1) }} ({{ $vendor->reviews_count }})
                                    @endif
                                </div>
                                <p class="mt-3 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($vendor->description, 110) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-6">{{ $vendors->links() }}</div>
            @endif
        </div>
    </div>
@endsection
