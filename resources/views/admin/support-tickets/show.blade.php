@extends('layouts.admin')

@section('title', 'Destek #'.$supportTicket->id)

@section('content')
    @php use App\Support\UiLabels; @endphp
    <nav class="mb-3"><a href="{{ route('admin.support-tickets.index') }}" class="small text-decoration-none text-muted">← Liste</a></nav>

    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-2">{{ $supportTicket->subject }}</h2>
            <p class="small text-muted mb-0">{{ $supportTicket->user?->name }} · {{ $supportTicket->user?->email }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2">{{ UiLabels::supportTicketStatus($supportTicket->status) }}</span>
            <form method="post" action="{{ route('admin.support-tickets.assign', $supportTicket) }}" class="d-inline">@csrf
                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">Üstlen</button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h3 class="h6 fw-bold mb-3">Durum</h3>
        <form method="post" action="{{ route('admin.support-tickets.update-status', $supportTicket) }}" class="row g-2 align-items-end">
            @csrf
            @method('PATCH')
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm rounded-3">
                    <option value="open" @selected($supportTicket->status==='open')>Açık</option>
                    <option value="pending" @selected($supportTicket->status==='pending')>Beklemede</option>
                    <option value="closed" @selected($supportTicket->status==='closed')>Kapalı</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-dark rounded-pill">Kaydet</button>
            </div>
        </form>
        @if($supportTicket->assignedAdmin)
            <p class="small text-muted mt-2 mb-0">Atanan: {{ $supportTicket->assignedAdmin->name }}</p>
        @endif
    </div>

    <h3 class="h6 fw-bold mb-3">Mesajlar</h3>
    <div class="d-flex flex-column gap-3 mb-4">
        @foreach($supportTicket->messages as $m)
            <div class="rounded-4 p-4 border {{ str_starts_with($m->body, '[Yönetici]') ? 'bg-primary-subtle border-primary-subtle' : 'bg-white shadow-sm' }}">
                <div class="d-flex justify-content-between small text-muted mb-2">
                    <strong class="text-dark">{{ $m->user?->name }}</strong>
                    <span>{{ $m->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <div class="small" style="white-space: pre-wrap;">{{ preg_replace('/^\[Yönetici\]\s*/', '', $m->body) }}</div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4">
        <h3 class="h6 fw-bold mb-3">Yanıt (yönetici)</h3>
        <form method="post" action="{{ route('admin.support-tickets.reply', $supportTicket) }}">
            @csrf
            <textarea name="body" rows="4" class="form-control rounded-3 mb-3" required maxlength="5000" placeholder="Yanıtınız müşteriye [Yönetici] öneki ile kaydedilir."></textarea>
            <button type="submit" class="btn btn-primary rounded-pill px-4">Gönder</button>
        </form>
    </div>
@endsection
