@extends('layouts.outdoor')
@section('title', $seekingAgencies ? 'Ajanslar' : 'Mecra sahipleri')
@section('content')
<h1 class="h5 mb-2">{{ $seekingAgencies ? 'Ajans dizini' : 'Mecra sahibi dizini' }}</h1>
<p class="small text-muted">İletişim bilgisi görünmez. Tek tuşla davet gönderin; karşı taraf onaylayınca mecralar paylaşılır.</p>
<form method="get" class="mb-3" style="max-width:360px;">
    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Ad veya şehir">
</form>
<div class="table-responsive card">
    <table class="table mb-0 align-middle">
        <thead><tr><th>Hesap</th><th>Şehir</th><th>Yayınlı pano</th><th></th></tr></thead>
        <tbody>
        @forelse($profiles as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td>{{ $p->city ?: '—' }}</td>
                <td>{{ $publishedCounts[$p->id] ?? 0 }}</td>
                <td class="text-end">
                    @if(in_array($p->id, $activeIds, true))
                        <span class="badge bg-success-subtle text-success">Bağlı</span>
                    @elseif(in_array($p->id, $pendingIds, true))
                        <span class="badge bg-warning text-dark">Onay bekliyor</span>
                    @else
                        <form method="POST" action="{{ route('outdoor-panel.directory.connect') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="vendor_id" value="{{ $p->id }}">
                            @if($seekingAgencies)
                                <label class="form-check form-check-inline small me-2">
                                    <input type="checkbox" name="exclusive" value="1" class="form-check-input"> Münhasır
                                </label>
                            @endif
                            <button class="btn btn-sm btn-primary">Bağlan</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-muted text-center py-4">Kayıt yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $profiles->links() }}</div>
@endsection
