@extends('layouts.account')
@section('title', __('panel.new_direct_quote') . ' - BaskıYeri')
@section('content')
<div class="mb-5">
    <a href="{{ route('customer.direct-quotes.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Doğrudan Tekliflere Dön
    </a>
</div>

<div class="max-w-xl">
    <h1 class="font-heading text-2xl font-bold tracking-tight text-ink mb-1">{{ __('panel.new_direct_quote') }}</h1>
    <p class="text-xs text-muted mb-6">Önceden sipariş verdiğiniz satıcıya özel iş detaylarını iletip fiyat teklifi isteyin.</p>

    <form method="post" action="{{ route('customer.direct-quotes.store') }}" class="by-card p-6 bg-surface border border-border space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.vendor') }} <span class="text-red-500">*</span></label>
            <select name="vendor_id" class="form-control text-xs" required>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
            </select>
            @if($vendors->isEmpty())
                <p class="text-[11px] text-muted mt-1.5">{{ __('panel.no_past_vendors') }}</p>
            @endif
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.title') }} <span class="text-red-500">*</span></label>
            <input name="title" class="form-control text-xs" required placeholder="Örn: 1000 Adet Özel Kesim Etiket">
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.body') }} <span class="text-red-500">*</span></label>
            <textarea name="body" class="form-control text-xs" rows="5" required placeholder="İşin detayları, ebat, adet, malzeme ve teslimat gereksinimleri..."></textarea>
        </div>
        <div class="pt-2 flex items-center justify-between">
            <button class="btn btn-cta text-xs py-2 px-6" @if($vendors->isEmpty()) disabled @endif>{{ __('panel.submit_for_approval') }}</button>
            <a href="{{ route('customer.direct-quotes.index') }}" class="btn btn-secondary text-xs">Vazgeç</a>
        </div>
    </form>
</div>
@endsection
