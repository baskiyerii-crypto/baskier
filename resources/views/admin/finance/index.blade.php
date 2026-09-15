@extends('layouts.admin')
@section('title', 'Finans')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
        <div class="card p-3"><div class="small text-muted">Ciro</div><strong>₺{{ number_format($metrics['revenue'], 2, ',', '.') }}</strong></div>
        <div class="card p-3"><div class="small text-muted">Komisyon</div><strong>₺{{ number_format($metrics['commission'], 2, ',', '.') }}</strong></div>
        <div class="card p-3"><div class="small text-muted">Abonelik geliri</div><strong>₺{{ number_format($metrics['subscription_income'], 2, ',', '.') }}</strong></div>
        <div class="card p-3"><div class="small text-muted">Kar marjı</div><strong>₺{{ number_format($metrics['margin'], 2, ',', '.') }}</strong></div>
    </div>
    <a href="{{ route('admin.finance.export') }}" class="btn btn-outline-primary btn-sm">CSV dışa aktar</a>
</div>

<div class="card p-4 mb-4" style="max-width:420px;">
    <h2 class="h6">Platform giderleri</h2>
    <form method="post" action="{{ route('admin.finance.expenses') }}" class="d-flex gap-2">
        @csrf
        <input type="number" step="0.01" min="0" name="platform_expenses" class="form-control" value="{{ $metrics['platform_expenses'] }}">
        <button class="btn btn-primary btn-sm">Kaydet</button>
    </form>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h2 class="h6">Ödemeler</h2>
            @forelse($payments as $p)
                <div class="small d-flex justify-content-between mb-2 border-bottom pb-2">
                    <span>#{{ $p->order?->order_number }} · {{ $p->provider }} · {{ $p->status }}</span>
                    <strong>₺{{ number_format($p->amount, 2, ',', '.') }}</strong>
                </div>
            @empty
                <p class="small text-muted">Ödeme kaydı yok.</p>
            @endforelse
            {{ $payments->links() }}
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h2 class="h6">Satıcı bakiye hareketleri</h2>
            @forelse($transactions as $tx)
                <div class="small d-flex justify-content-between mb-2 border-bottom pb-2">
                    <span>{{ $tx->vendor?->name }} · {{ $tx->description }}</span>
                    <strong>₺{{ number_format($tx->amount, 2, ',', '.') }}</strong>
                </div>
            @empty
                <p class="small text-muted">Hareket yok.</p>
            @endforelse
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
