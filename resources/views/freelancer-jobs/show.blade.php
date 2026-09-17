@extends('layouts.app')

@section('title', $job->title . ' - Hizmet Talebi - BaskıYeri')

@section('content')
<div class="by-container py-8 md:py-12">
    <div class="mb-5">
        <a href="{{ route('service-requests.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Tüm Hizmet Taleplerine Dön
        </a>
    </div>

    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <div>
            <div class="by-card p-6 md:p-8 bg-surface border border-border">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-canvas text-ink border border-border">
                        {{ \App\Support\FreelancerCategories::label($job->category) }}
                    </span>
                    <span class="text-xs text-muted">Talep #{{ $job->id }} · {{ $job->created_at->format('d.m.Y') }}</span>
                </div>

                <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink mb-4">
                    {{ $job->title }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 py-3 border-y border-border text-xs text-muted mb-6">
                    @if($job->user)
                        <div class="flex items-center gap-1.5">
                            <span class="text-muted">İlan Sahibi:</span>
                            <strong class="text-ink font-semibold">{{ $job->user->name }}</strong>
                        </div>
                    @endif
                    @if($job->budget_min || $job->budget_max)
                        <div class="flex items-center gap-1.5">
                            <span class="text-muted">Bütçe:</span>
                            <strong class="text-ink font-semibold">
                                ₺{{ $job->budget_min ? number_format($job->budget_min, 0, ',', '.') : '0' }}
                                – ₺{{ $job->budget_max ? number_format($job->budget_max, 0, ',', '.') : 'Limit Yok' }}
                            </strong>
                        </div>
                    @endif
                    @if($job->bids_count !== null)
                        <div class="flex items-center gap-1.5">
                            <span class="text-muted">Gelen Teklif:</span>
                            <strong class="text-ink font-semibold">{{ $job->bids_count }}</strong>
                        </div>
                    @endif
                </div>

                <div class="prose max-w-none text-sm text-ink leading-relaxed">
                    {!! nl2br(e($job->description)) !!}
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="by-card p-6 bg-surface border border-border">
                @guest
                    <div class="text-center py-4">
                        <div class="w-10 h-10 mx-auto rounded-full bg-canvas flex items-center justify-center text-muted mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-ink mb-1">Teklif Vermek İçin Giriş Yapın</h3>
                        <p class="text-xs text-muted mb-4">Bu talebe özel fiyat ve süre teklifi sunmak için üye girişi yapmalısınız.</p>
                        <a href="{{ route('login') }}" class="btn btn-cta w-full text-xs">Giriş Yap</a>
                    </div>
                @else
                    @if(auth()->id() === $job->user_id)
                        <h3 class="font-heading text-base font-bold text-ink mb-3">Gelen Teklifler ({{ $job->bids->count() }})</h3>
                        <div class="space-y-3">
                            @forelse($job->bids as $bid)
                                <div class="p-3.5 rounded-xl border border-border bg-canvas/40 text-xs">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <span class="font-bold text-ink">{{ $bid->user?->name }}</span>
                                        <span class="font-extrabold text-ink">₺{{ number_format($bid->amount, 2, ',', '.') }}</span>
                                    </div>
                                    @if($bid->delivery_days)
                                        <div class="text-[11px] text-muted mb-1.5">Termin: {{ $bid->delivery_days }} gün</div>
                                    @endif
                                    @if($bid->proposal)
                                        <div class="text-muted text-[11px] leading-relaxed mb-2">{{ Str::limit($bid->proposal, 120) }}</div>
                                    @endif
                                    @if($bid->status === 'pending')
                                        <form action="{{ route('service-requests.select-bid', [$job, $bid]) }}" method="post" class="mt-2" onsubmit="return confirm('Bu teklifi seçmek sipariş oluşturur. Devam edilsin mi?');">
                                            @csrf
                                            <button type="submit" class="btn btn-cta w-full text-xs py-1.5">Bu Teklifi Seç ve Başlat</button>
                                        </form>
                                    @else
                                        <x-badge variant="neutral">{{ \App\Support\UiLabels::status($bid->status) }}</x-badge>
                                    @endif
                                </div>
                            @empty
                                <div class="p-4 rounded-xl bg-canvas border border-border text-center text-xs text-muted">
                                    Henüz teklif gelmedi.
                                </div>
                            @endforelse
                        </div>
                    @else
                        <h3 class="font-heading text-base font-bold text-ink mb-1">Teklif Ver</h3>
                        <p class="text-xs text-muted mb-4">Bu iş için ücret ve teslim süresi belirleyin.</p>
                        <form action="{{ route('service-requests.bid', $job) }}" method="post" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-1">Tutar (₺) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control text-sm" required placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-1">Teslim Süresi (İş Günü)</label>
                                <input type="number" name="delivery_days" class="form-control text-sm" min="1" placeholder="Örn: 2">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-1">Öneri / Açıklama</label>
                                <textarea name="proposal" class="form-control text-sm min-h-[90px]" rows="3" placeholder="İş planınız ve detaylar..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-cta w-full text-xs py-2.5 mt-2">
                                Teklifi Gönder
                            </button>
                        </form>
                    @endif
                @endguest
            </div>
        </aside>
    </div>
</div>
@endsection
