@extends('layouts.outdoor')
@section('title', 'Açık hava özeti')
@section('content')
@php
    $isAgency = false;
    $isMuni = false;
    try {
        $isAgency = (bool) $vendor->isOutdoorAgency();
        $isMuni = (bool) $vendor->isMunicipalityOwner();
    } catch (\Throwable) {
    }
    $panelTitle = $isAgency ? 'Ajans paneli' : ($isMuni ? 'Belediye mecra paneli' : 'Mecra sahibi paneli');
@endphp
<h1 class="h5 mb-3">{{ $panelTitle }}</h1>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-3"><div class="small text-muted">Bekleyen talepler</div><div class="h4 mb-0">{{ $pendingRequests }}</div></div>
    </div>
    @if(! $isAgency)
    <div class="col-md-4">
        <div class="card p-3"><div class="small text-muted">Envanter</div><div class="h4 mb-0">{{ $inventoryCount }}</div></div>
    </div>
    @endif
    <div class="col-md-4">
        <div class="card p-3"><div class="small text-muted">Aktif temsil bağları</div><div class="h4 mb-0">{{ $representationCount }}</div></div>
    </div>
</div>
@endsection
