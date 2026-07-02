@extends('layouts.admin')
@section('title', 'Ayarlar')
@section('content')
<div class="card p-4" style="max-width:520px;">
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Komisyon oranı (%)</label>
            <input type="number" name="commission_rate" class="form-control" value="{{ old('commission_rate', $commission_rate) }}" min="0" max="100" step="0.01" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Hakediş dağılım günü (ayın kaçı)</label>
            <input type="number" name="payout_day_of_month" class="form-control" value="{{ old('payout_day_of_month', $payout_day_of_month) }}" min="1" max="28" required>
            <div class="form-text">Örn: 5 = her ayın 5'inde listelenir.</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Komisyon bekleme süresi (gün)</label>
            <input type="number" name="commission_wait_days" class="form-control" value="{{ old('commission_wait_days', $commission_wait_days) }}" min="0" max="90" required>
            <div class="form-text">Teslimden sonra kaç gün beklenir (örn: 15).</div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Tabela görüşme ücreti (₺)</label>
            <input type="number" name="meeting_fee" class="form-control" value="{{ old('meeting_fee', $meeting_fee) }}" min="0" max="1000" step="0.01" required>
        </div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
    </form>
</div>
@endsection
