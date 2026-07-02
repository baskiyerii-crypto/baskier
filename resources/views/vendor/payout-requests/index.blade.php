@extends('layouts.vendor')
@section('title', 'Ödeme talepleri')
@section('content')
@php use App\Support\UiLabels; @endphp

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100" style="background: linear-gradient(145deg, #fff7ed, #fff);">
            <h2 class="h6 fw-bold mb-2">Yeni talep</h2>
            <p class="small text-muted mb-3">Mevcut bakiye: <strong class="text-success">₺{{ number_format($vendor->balance, 2, ',', '.') }}</strong></p>
            <form method="post" action="{{ route('vendor.payout-requests.store') }}">
                @csrf
                <label class="form-label small fw-medium">Tutar (₺)</label>
                <input type="number" name="amount" step="0.01" min="1" class="form-control rounded-3 mb-3" required placeholder="ör. 500">
                <button type="submit" class="btn btn-warning rounded-pill fw-semibold w-100">Talep oluştur</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="p-3 border-bottom bg-white">
                <h2 class="h6 fw-bold mb-0">Geçmiş talepler</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light"><tr><th>Tutar</th><th>Durum</th><th>Tarih</th></tr></thead>
                    <tbody>
                        @forelse($requests as $r)
                            <tr>
                                <td class="fw-semibold">₺{{ number_format($r->amount, 2, ',', '.') }}</td>
                                <td><span class="badge rounded-pill bg-light text-dark border">{{ UiLabels::payoutRequestStatus($r->status) }}</span></td>
                                <td class="text-muted">{{ $r->created_at->format('d.m.Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Henüz talep yok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
