@extends('layouts.app')

@section('title', 'Satıcılar - BaskıYeri Pazaryeri')

@section('content')
    <div class="content-shell">
    <h1 class="h5 mb-3">Satıcılar</h1>

    @if(isset($businessTypes) && $businessTypes->isNotEmpty())
        <form method="get" action="{{ route('vendors.index') }}" class="row g-2 align-items-end mb-4">
            <div class="col-auto">
                <label class="form-label small text-muted mb-0">İş kolu</label>
                <select name="business_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Tümü</option>
                    @foreach($businessTypes as $bt)
                        <option value="{{ $bt->slug }}" @selected(request('business_type') === $bt->slug)>{{ $bt->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    @endif

    @if($vendors->isEmpty())
        <p class="text-muted small">Satıcı bulunamadı.</p>
    @else
        <div class="row g-3 g-md-4">
            @foreach($vendors as $vendor)
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="{{ route('vendors.show', $vendor->slug) }}" class="text-decoration-none text-dark">
                        <div class="bg-white rounded-4 shadow-sm p-3 p-md-4 h-100 d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                                    {{ mb_substr($vendor->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $vendor->name }}</div>
                                    @if($vendor->rating_average)
                                        <div class="small text-warning">★ {{ number_format($vendor->rating_average, 1) }} ({{ $vendor->reviews_count }})</div>
                                    @endif
                                    @if($vendor->email)
                                        <div class="small text-muted">{{ $vendor->email }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="small text-muted flex-grow-1">
                                {{ \Illuminate\Support\Str::limit($vendor->description, 80) }}
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $vendors->links() }}
        </div>
    @endif
    </div>
@endsection

