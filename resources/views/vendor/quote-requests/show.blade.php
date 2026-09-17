@extends('layouts.vendor')

@section('title', 'Talep #' . $quoteRequest->id . ' - Satıcı Paneli')

@section('content')
<div class="mb-5">
    <a href="{{ route($quoteRequest->request_type === 'freelancer' ? 'vendor.freelancer.index' : 'vendor.quote-requests.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Taleplere Dön
    </a>
</div>

<div class="by-card p-6 bg-surface border border-border mb-6">
    <div class="flex items-center gap-2 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-muted">Talep #{{ $quoteRequest->id }}</span>
        <span class="text-xs text-muted">·</span>
        <span class="text-xs text-muted">{{ $quoteRequest->category?->name }}</span>
    </div>
    <h1 class="font-heading text-2xl font-bold tracking-tight text-ink mb-2">{{ $quoteRequest->title }}</h1>
    @if($quoteRequest->description)
        <div class="text-xs text-ink whitespace-pre-line leading-relaxed mt-3 pt-3 border-t border-border">
            {{ $quoteRequest->description }}
        </div>
    @endif

    @if($quoteRequest->items && $quoteRequest->items->isNotEmpty())
        <div class="mt-6 pt-4 border-t border-border">
            <h2 class="text-xs font-bold uppercase tracking-wider text-muted mb-3">İş Kalemleri</h2>
            <div class="space-y-3">
                @foreach($quoteRequest->items as $it)
                    <div class="p-4 rounded-xl border border-border bg-canvas/40 text-xs">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <div class="font-bold text-ink">
                                    {{ $it->product?->name ?? $it->category?->name ?? 'Kalem' }}
                                </div>
                                <div class="text-muted mt-0.5">
                                    {{ $it->category?->name }}
                                    @if($it->quantity)
                                        · <strong class="text-ink">{{ $it->quantity }} {{ $it->unit ? $it->unit : 'adet' }}</strong>
                                    @endif
                                </div>
                            </div>
                            @if($it->files && $it->files->isNotEmpty())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-canvas text-ink border border-border">{{ $it->files->count() }} dosya</span>
                            @endif
                        </div>
                        @if($it->spec)
                            <div class="mt-2 p-2.5 rounded bg-surface border border-border text-ink whitespace-pre-wrap">
                                {{ $it->spec }}
                            </div>
                        @endif
                        @if($it->files && $it->files->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($it->files as $f)
                                    <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-surface text-xs font-medium text-ink hover:border-cta hover:text-cta transition-colors"
                                       href="{{ asset('storage/'.$f->path) }}"
                                       target="_blank" rel="noopener">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        {{ \Illuminate\Support\Str::limit($f->original_name ?? basename($f->path), 22) }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($hasPaidMeeting)
        <div class="mt-6 pt-4 border-t border-border grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div><strong>Bölge:</strong> {{ $quoteRequest->city ?? '—' }} {{ $quoteRequest->district ?? '' }}</div>
            <div><strong>Adres:</strong> {{ $quoteRequest->address ?? '—' }}</div>
            <div><strong>İletişim:</strong> {{ $quoteRequest->contact_phone ?? '—' }}</div>
        </div>
    @endif
</div>

@if(!$hasPaidMeeting)
    <div class="by-card p-6 bg-surface border border-border mb-6">
        <h2 class="font-heading text-base font-bold text-ink mb-1">Görüşme ve İletişim Erişimi</h2>
        <p class="text-xs text-muted mb-4">Talep detayını (adres, doğrudan iletişim) görmek ve teklif vermek için görüşme ücreti (₺{{ number_format(\App\Models\Setting::meetingFee(), 2, ',', '.') }}) bakiyenizden düşülür.</p>
        <form method="POST" action="{{ route('vendor.quote-requests.accept-meeting', $quoteRequest) }}">
            @csrf
            <button type="submit" class="btn btn-cta text-xs py-2 px-6">Görüşme Hakkı Al (₺{{ number_format(\App\Models\Setting::meetingFee(), 2, ',', '.') }})</button>
        </form>
    </div>
@else
    <div class="by-card p-6 bg-surface border border-border">
        <h2 class="font-heading text-base font-bold text-ink mb-1">Teklif Ver</h2>
        @if($myQuote)
            <div class="p-3 mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-950">
                Verdiğiniz teklif: <strong class="text-emerald-900">₺{{ number_format($myQuote->amount, 2, ',', '.') }}</strong> @if($myQuote->delivery_days)(Termin: {{ $myQuote->delivery_days }} gün)@endif. Güncellemek için yeni değerler girebilirsiniz.
            </div>
        @else
            <p class="text-xs text-muted mb-4">Müşteriye iletilecek net tutar ve termin süresini girin.</p>
        @endif

        <form method="POST" action="{{ route('vendor.quote-requests.submit-quote', $quoteRequest) }}" class="space-y-4 max-w-lg">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Tutar (₺) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0" class="form-control text-xs" value="{{ old('amount', $myQuote->amount ?? '') }}" required placeholder="0.00">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Termin Süresi (İş Günü)</label>
                    <input type="number" name="delivery_days" min="1" class="form-control text-xs" value="{{ old('delivery_days', $myQuote->delivery_days ?? '') }}" placeholder="Örn: 3">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Satıcı Notu</label>
                <textarea name="note" class="form-control text-xs" rows="3" placeholder="Teklifinize dair detaylar, kullanılan malzeme vb...">{{ old('note', $myQuote->note ?? '') }}</textarea>
            </div>
            <button type="submit" class="btn btn-cta text-xs py-2 px-6">{{ $myQuote ? 'Teklifi Güncelle' : 'Teklifi Gönder' }}</button>
        </form>
    </div>
@endif
@endsection
