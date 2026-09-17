@extends('layouts.account')

@section('title', 'Siparişlerim')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;
@endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h5 mb-0 fw-bold">Siparişlerim</h1>
        <a href="{{ route('products.index') }}" class="btn btn-warning btn-sm rounded-pill">+ Alışverişe Başla</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-4 shadow-sm border overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Sipariş No</th>
                        <th>Tarih</th>
                        <th>Tür</th>
                        <th>Satıcı / Mağaza</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        @php
                            $typeLabel = match($o->type) {
                                'product' => 'Pazaryeri',
                                'quote' => 'Özel Teklif',
                                'freelancer' => 'Freelancer',
                                default => ucfirst($o->type ?? 'Sipariş'),
                            };
                            $badgeClass = match($o->status) {
                                OrderStatus::CONFIRMED, OrderStatus::PENDING, 'paid' => 'bg-primary text-white',
                                OrderStatus::DESIGN_REVIEW => 'bg-warning text-dark',
                                OrderStatus::IN_PRODUCTION => 'bg-info text-dark',
                                OrderStatus::READY_TO_SHIP => 'bg-secondary text-white',
                                OrderStatus::SHIPPED => 'bg-primary-subtle text-primary border border-primary',
                                OrderStatus::DELIVERED, OrderStatus::COMPLETED => 'bg-success text-white',
                                OrderStatus::CANCELLED => 'bg-danger text-white',
                                default => 'bg-secondary text-white',
                            };
                        @endphp
                        <tr>
                            <td class="fw-bold">#{{ $o->order_number }}</td>
                            <td class="text-muted">{{ $o->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $typeLabel }}</span>
                            </td>
                            <td>{{ $o->vendor?->name ?? 'BaskıYeri' }}</td>
                            <td class="fw-bold text-success">₺{{ number_format($o->subtotal, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge rounded-pill px-2.5 py-1 {{ $badgeClass }}">
                                    {{ UiLabels::orderStatus($o->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('account.orders.show', $o) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">Detay →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted text-center p-5">
                                <div class="mb-2">📦</div>
                                Henüz verilmiş bir siparişiniz bulunmuyor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
@endsection
