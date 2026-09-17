@extends('layouts.account')

@section('title', $supportTicket->subject)

@section('content')
    @php use App\Support\UiLabels; @endphp
    <nav class="mb-3"><a href="{{ route('account.support.index') }}" class="small text-muted text-decoration-none">← Taleplerim</a></nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">{{ $supportTicket->subject }}</h1>
            <span class="badge rounded-pill px-3 py-2 {{ $supportTicket->status === 'closed' ? 'bg-secondary-subtle text-secondary' : 'bg-warning-subtle text-dark' }}">
                {{ UiLabels::supportTicketStatus($supportTicket->status) }}
            </span>
        </div>
    </div>

    <div class="d-flex flex-column gap-3 mb-4">
        @foreach($supportTicket->messages as $m)
            <div class="rounded-4 p-4 {{ str_starts_with($m->body, '[Yönetici]') ? 'bg-primary-subtle border border-primary-subtle' : 'bg-white border shadow-sm' }}">
                <div class="d-flex justify-content-between small text-muted mb-2">
                    <strong class="text-dark">{{ $m->user?->name ?? 'Kullanıcı' }}</strong>
                    <span>{{ $m->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <div class="small" style="white-space: pre-wrap;">{{ preg_replace('/^\[Yönetici\]\s*/', '', $m->body) }}</div>
            </div>
        @endforeach
    </div>

    @if($supportTicket->status !== 'closed')
        <div class="rounded-4 border-0 shadow-sm p-4 bg-white">
            <h2 class="h6 fw-semibold mb-3">Yanıt yaz</h2>
            <form method="post" action="{{ route('account.support.reply', $supportTicket) }}">
                @csrf
                <textarea name="body" rows="4" class="form-control rounded-3 mb-3 @error('body') is-invalid @enderror" required maxlength="5000" placeholder="Mesajınız">{{ old('body') }}</textarea>
                @error('body')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold">Gönder</button>
            </form>
        </div>
    @else
        <p class="small text-muted">Bu talep kapatılmıştır.</p>
    @endif
@endsection
