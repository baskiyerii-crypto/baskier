@extends('layouts.outdoor')
@section('title', 'Açık hava özeti')
@section('content')
@php
    $isAgency = $isAgency ?? false;
    $isMuni = false;
    try {
        $isMuni = (bool) $vendor->isMunicipalityOwner();
    } catch (\Throwable) {
    }
    $panelTitle = $isAgency ? 'Ajans paneli' : ($isMuni ? 'Belediye mecra paneli' : 'Mecra sahibi paneli');
@endphp
<h1 class="h5 mb-3">{{ $panelTitle }}</h1>
@include('partials.kyc-cta')
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card p-3"><div class="small text-muted">Bu ay ciro</div><div class="h4 mb-0">₺{{ number_format($monthRevenue ?? 0, 2, ',', '.') }}</div></div>
    </div>
    <div class="col-md-3">
        <div class="card p-3"><div class="small text-muted">Cüzdan / çekilebilir</div><div class="h5 mb-0">₺{{ number_format($vendor->balance, 2, ',', '.') }}</div><div class="small text-muted">Net ₺{{ number_format($availableBalance ?? 0, 2, ',', '.') }}</div></div>
    </div>
    <div class="col-md-3">
        <div class="card p-3"><div class="small text-muted">Bekleyen talepler</div><div class="h4 mb-0">{{ $pendingRequests }}</div></div>
    </div>
    <div class="col-md-3">
        <div class="card p-3"><div class="small text-muted">Bu ay kabul</div><div class="h4 mb-0">{{ $acceptedMonth ?? 0 }}</div></div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card p-3"><div class="small text-muted">{{ $isAgency ? 'Temsil edilen pano' : 'Envanter' }}</div><div class="h4 mb-0">{{ $inventoryCount }}</div><div class="small text-muted">Yayında {{ $publishedCount ?? 0 }}</div></div>
    </div>
    <div class="col-md-3">
        <div class="card p-3"><div class="small text-muted">Aktif temsil bağları</div><div class="h4 mb-0">{{ $representationCount }}</div></div>
    </div>
    <div class="col-md-3">
        <div class="card p-3"><div class="small text-muted">Rezerve iş</div><div class="h4 mb-0">{{ $occupancyBooked ?? 0 }}</div><div class="small text-muted">{{ $occupancyDays ?? 0 }} gün</div></div>
    </div>
</div>
@if(! $isAgency && !empty($agencyRows))
<div class="card p-3 mb-3">
    <h2 class="h6">Ajans performansı (ay)</h2>
    <table class="table table-sm mb-0">
        <thead><tr><th>Ajans</th><th>Münhasır</th><th>Ciro</th></tr></thead>
        <tbody>
        @foreach($agencyRows as $row)
            <tr><td>{{ $row['name'] }}</td><td>{{ $row['exclusive'] ? 'Evet' : 'Hayır' }}</td><td>₺{{ number_format($row['revenue'], 2, ',', '.') }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@if(! $isAgency && !empty($staffRows))
<div class="card p-3">
    <h2 class="h6">Saha ekibi</h2>
    <table class="table table-sm mb-0">
        <thead><tr><th>Kişi</th><th>Rol</th><th>Atanan iş</th><th>Geçerli kanıt</th></tr></thead>
        <tbody>
        @foreach($staffRows as $row)
            <tr><td>{{ $row['name'] }}</td><td>{{ $row['role'] }}</td><td>{{ $row['jobs'] }}</td><td>{{ $row['proved'] }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
