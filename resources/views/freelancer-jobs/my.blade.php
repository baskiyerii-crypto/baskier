@extends('layouts.app')

@section('title', 'Hizmet Taleplerim')

@section('content')
<div class="content-shell py-4">
    <h1 class="h5 mb-4">Hizmet Taleplerim</h1>
    <div class="bg-white rounded-4 shadow-sm table-responsive">
        <table class="table table-hover mb-0 small">
            <thead><tr><th>Başlık</th><th>Durum</th><th>Teklif</th><th></th></tr></thead>
            <tbody>
                @forelse($jobs as $j)
                    <tr>
                        <td>{{ Str::limit($j->title, 50) }}</td>
                        <td>{{ \App\Support\UiLabels::status($j->status) }}</td>
                        <td>{{ $j->bids_count }}</td>
                        <td class="text-end"><a href="{{ route('service-requests.show', $j) }}" class="btn btn-sm btn-outline-dark">Görüntüle</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted p-4">Henüz hizmet talebiniz bulunmuyor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $jobs->links() }}</div>
</div>
@endsection
