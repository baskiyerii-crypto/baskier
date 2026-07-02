@extends('layouts.account')

@section('title', 'Destek talepleri')

@section('content')
    @php use App\Support\UiLabels; @endphp
    <nav class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('customer.dashboard') }}" class="small text-muted text-decoration-none">← Özet</a>
            <h1 class="h4 fw-bold mt-1 mb-0">Destek talepleri</h1>
            <p class="small text-muted mb-0">Sipariş ve platform ile ilgili sorularınızı buradan takip edin.</p>
        </div>
        <a href="{{ route('account.support.create') }}" class="btn btn-warning rounded-pill px-4 fw-semibold shadow-sm">+ Yeni talep</a>
    </nav>

    @if($tickets->isEmpty())
        <div class="text-center py-5 rounded-4 border bg-white">
            <p class="text-muted mb-3">Henüz destek talebiniz yok.</p>
            <a href="{{ route('account.support.create') }}" class="btn btn-outline-dark rounded-pill">İlk talebi oluştur</a>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach($tickets as $t)
                <a href="{{ route('account.support.show', $t) }}" class="text-decoration-none text-dark">
                    <div class="rounded-4 border-0 shadow-sm p-4 bg-white border-start border-4 border-warning" style="transition: transform .12s ease, box-shadow .12s ease;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 28px rgba(15,23,42,.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <div class="fw-semibold fs-6">{{ $t->subject }}</div>
                                <div class="small text-muted mt-1">{{ $t->created_at->translatedFormat('d F Y, H:i') }}</div>
                            </div>
                            <span class="badge rounded-pill px-3 py-2 {{ $t->status === 'closed' ? 'bg-secondary-subtle text-secondary' : ($t->status === 'pending' ? 'bg-info-subtle text-primary' : 'bg-warning-subtle text-dark') }}">
                                {{ UiLabels::supportTicketStatus($t->status) }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-4">{{ $tickets->links() }}</div>
    @endif
@endsection
