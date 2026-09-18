@extends($layout ?? 'layouts.vendor')
@section('title', 'Bakiye')
@section('content')
@php $isOutdoorPanel = $isOutdoorPanel ?? false; @endphp
<div class="card p-4 mb-4">
    <h2 class="h6 mb-2">{{ $isOutdoorPanel ? 'Açık hava bakiyesi' : 'Tabela gorusme bakiyesi' }}</h2>
    <p class="h4 text-success">TL {{ number_format($vendor->balance, 2, ',', '.') }}</p>
    @unless($isOutdoorPanel)
    <p class="small text-muted">Gorusme ucreti: TL {{ number_format($meetingFee, 2, ',', '.') }}</p>
    @endunless
    <form method="POST" action="{{ route($isOutdoorPanel ? 'outdoor-panel.balance.topup' : 'vendor.balance.topup') }}" class="d-flex gap-2 align-items-end">
        @csrf
        <div>
            <label class="form-label small">Tutar (TL)</label>
            <input type="number" name="amount" min="10" max="10000" class="form-control form-control-sm" style="width:100px;" value="100" required>
        </div>
        <button type="submit" class="btn btn-success btn-sm">Bakiye yukle (demo)</button>
    </form>
</div>
<div class="card p-4">
    <h3 class="h6 mb-3">Hareketler</h3>
    @if($transactions->isEmpty())
        <p class="text-muted small">Hareket yok.</p>
    @else
        <table class="table table-sm mb-0">
            <thead><tr><th>Tarih</th><th>Aciklama</th><th>Tutar</th></tr></thead>
            <tbody>
                @foreach($transactions as $t)
                    <tr>
                        <td>{{ $t->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $t->description ?? $t->type }}</td>
                        <td class="{{ $t->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $t->amount >= 0 ? '+' : '' }}TL {{ number_format($t->amount, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
