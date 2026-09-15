@extends('layouts.vendor')
@section('title', 'Tabela')
@section('content')
<div class="card p-4 mb-3">
    <p class="mb-1">Görüşme ücreti: <strong>₺{{ number_format($fee, 2, ',', '.') }}</strong></p>
    <p class="small text-muted mb-0">Her yeni tabela görüşmesi bakiyenizden düşülür. Aylık abonelik ayrıca Modüller sayfasından yönetilir.</p>
</div>
<div class="card p-4">
    <h2 class="h6">Görüşmeler</h2>
    @forelse($conversations as $c)
        <div class="d-flex justify-content-between border-bottom py-2 small">
            <span>Müşteri #{{ $c->user_id }}</span>
            <a href="{{ route('vendor.messages.show', $c) }}">Mesaja git</a>
        </div>
    @empty
        <p class="small text-muted mb-0">Henüz görüşme yok.</p>
    @endforelse
    {{ $conversations->links() }}
</div>
@endsection
