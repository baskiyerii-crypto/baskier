@extends('layouts.vendor')
@section('title', 'Asım işleri')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<h1 class="h5">Rezerve asım işleri</h1>
@if($jobs->isEmpty())
    <div class="card p-4 text-muted">İş yok.</div>
@else
    @foreach($jobs as $job)
        <div class="card p-3 mb-3">
            <strong>{{ $job->inventory?->title }}</strong>
            <div class="small">{{ $job->starts_on->toDateString() }} – {{ $job->ends_on->toDateString() }}</div>
            @if($role !== 'field')
                <form method="POST" action="{{ route('vendor.outdoor.jobs.assign', $job) }}" class="d-flex gap-2 mt-2">
                    @csrf
                    <input name="assigned_user_id" class="form-control form-control-sm" placeholder="Kullanıcı ID" required>
                    <button class="btn btn-outline-secondary btn-sm">Ata</button>
                </form>
            @endif
            <form method="POST" action="{{ route('vendor.outdoor.jobs.proof', $job) }}" enctype="multipart/form-data" class="mt-2">
                @csrf
                <input type="file" name="photo" accept="image/*" capture="environment" required class="form-control form-control-sm mb-1">
                @include('partials.gps-capture', [
                    'gpsMode' => 'submit',
                    'gpsUseOld' => false,
                    'gpsLat' => '',
                    'gpsLng' => '',
                    'gpsButton' => 'Konumu al',
                    'gpsHint' => 'Fotoğrafı panonun yanında çekin. Kanıt yükleyince GPS otomatik alınır.',
                ])
                <button class="btn btn-primary btn-sm">Kanıt yükle</button>
            </form>
            @foreach($job->proofs as $p)
                <div class="small mt-1">Kanıt #{{ $p->id }} · {{ $p->distance_m }} m · {{ $p->is_valid ? 'geçerli' : 'yarıçap dışı' }}</div>
            @endforeach
        </div>
    @endforeach
    {{ $jobs->links() }}
@endif
@endsection
