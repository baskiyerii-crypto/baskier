@extends('layouts.admin')

@section('title', 'Destek talepleri')

@section('content')
    @php use App\Support\UiLabels; @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-muted fw-semibold mb-1" style="letter-spacing:.08em;">Destek</p>
            <h2 class="h4 fw-bold mb-0">Talepler</h2>
        </div>
        <form method="get" class="d-flex gap-2 align-items-center">
            <select name="status" class="form-select form-select-sm rounded-3" style="width:auto" onchange="this.form.submit()">
                <option value="">Tüm durumlar</option>
                <option value="open" @selected(request('status')==='open')>Açık</option>
                <option value="pending" @selected(request('status')==='pending')>Beklemede</option>
                <option value="closed" @selected(request('status')==='closed')>Kapalı</option>
            </select>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Konu</th><th>Kullanıcı</th><th>Durum</th><th>Tarih</th><th></th></tr></thead>
                <tbody>
                    @forelse($tickets as $t)
                        <tr>
                            <td class="fw-medium">{{ Str::limit($t->subject, 48) }}</td>
                            <td class="small">{{ $t->user?->email }}</td>
                            <td><span class="badge rounded-pill bg-primary-subtle text-primary">{{ UiLabels::supportTicketStatus($t->status) }}</span></td>
                            <td class="small text-muted">{{ $t->created_at->format('d.m.Y H:i') }}</td>
                            <td class="text-end"><a href="{{ route('admin.support-tickets.show', $t) }}" class="btn btn-sm btn-outline-primary rounded-pill">Aç</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">Kayıt yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $tickets->links() }}</div>
@endsection
