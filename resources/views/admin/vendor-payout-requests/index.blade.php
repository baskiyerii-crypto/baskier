@extends('layouts.admin')

@section('title', 'Satıcı ödeme talepleri')

@section('content')
    @php use App\Support\UiLabels; @endphp
    <div class="mb-4">
        <p class="text-uppercase small text-muted fw-semibold mb-1" style="letter-spacing:.08em;">Finans</p>
        <h2 class="h4 fw-bold mb-0">Satıcı bakiye çekim talepleri</h2>
        <p class="small text-muted mb-0">Onaylandığında tutar satıcı bakiyesinden düşer.</p>
    </div>

    <form method="get" class="mb-3">
        <select name="status" class="form-select form-select-sm rounded-3 d-inline-block" style="max-width:200px" onchange="this.form.submit()">
            <option value="">Tümü</option>
            <option value="pending" @selected(request('status')==='pending')>Bekleyen</option>
            <option value="approved" @selected(request('status')==='approved')>Onaylı</option>
            <option value="rejected" @selected(request('status')==='rejected')>Red</option>
        </select>
    </form>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Satıcı</th><th>Tutar</th><th>IBAN</th><th>Durum</th><th>Talep tarihi</th><th>Onay / red</th><th>İşlem</th></tr>
                </thead>
                <tbody>
                    @forelse($requests as $r)
                        <tr>
                            <td class="fw-medium">{{ $r->vendor?->name }}</td>
                            <td>₺{{ number_format($r->amount, 2, ',', '.') }}</td>
                            <td class="small font-monospace">{{ $r->iban ?: '—' }}<div class="text-muted">{{ $r->account_holder }}</div></td>
                            <td><span class="badge rounded-pill bg-light text-dark border">{{ UiLabels::payoutRequestStatus($r->status) }}</span>@if(($r->source ?? '')==='auto')<div class="small text-muted">Otomatik</div>@endif</td>
                            <td class="small text-muted">{{ optional($r->requested_at ?? $r->created_at)->format('d.m.Y H:i') }}</td>
                            <td class="small text-muted">{{ optional($r->approved_at ?: $r->rejected_at ?: $r->processed_at)->format('d.m.Y H:i') ?: '—' }}</td>
                            <td>
                                @if($r->status === 'pending')
                                    <div class="d-flex flex-wrap gap-2">
                                        <form method="post" action="{{ route('admin.vendor-payout-requests.approve', $r) }}" class="d-inline" onsubmit="return confirm('Onaylansın mı? Bakiyeden düşülecek.');">
                                            @csrf
                                            <input type="hidden" name="admin_note" value="">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill">Onayla</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.vendor-payout-requests.reject', $r) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Reddet</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="small text-muted">{{ Str::limit($r->admin_note, 24) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">Kayıt yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $requests->links() }}</div>
@endsection
