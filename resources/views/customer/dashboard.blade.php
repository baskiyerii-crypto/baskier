@extends('layouts.account')

@section('title', 'Hesabım - BaskıYeri')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-muted">Müşteri Paneli</p>
            <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink mt-1">Merhaba, {{ $user->name }}</h1>
            <p class="text-sm text-muted mt-1">Siparişleriniz, fiyat teklifleriniz ve hesap detaylarınız</p>
        </div>
        <div class="flex flex-wrap gap-2.5">
            <a href="{{ route('products.index') }}" class="btn btn-secondary text-xs">
                Ürünleri İncele
            </a>
            <a href="{{ route('quote-requests.create') }}" class="btn btn-cta text-xs">
                + Teklif Talebi
            </a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="by-card p-5 bg-surface border border-border">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-muted">Siparişler</span>
                <span class="p-2 rounded-lg bg-canvas text-ink">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-3xl font-extrabold text-ink">{{ $ordersCount }}</div>
                <a href="{{ route('account.orders.index') }}" class="text-xs font-semibold text-cta hover:underline">Görüntüle →</a>
            </div>
        </div>

        <div class="by-card p-5 bg-surface border border-border">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-muted">Sepet</span>
                <span class="p-2 rounded-lg bg-canvas text-ink">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-3xl font-extrabold text-ink">{{ $cartCount }}</div>
                <a href="{{ route('cart.index') }}" class="text-xs font-semibold text-cta hover:underline">Sepete Git →</a>
            </div>
        </div>

        <div class="by-card p-5 bg-surface border border-border">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-muted">Favoriler</span>
                <span class="p-2 rounded-lg bg-canvas text-ink">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-3xl font-extrabold text-ink">{{ $favoritesCount }}</div>
                <a href="{{ route('favorites.index') }}" class="text-xs font-semibold text-cta hover:underline">Listele →</a>
            </div>
        </div>

        <div class="by-card p-5 bg-surface border border-border flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-muted">Hızlı Sipariş</span>
                <span class="p-2 rounded-lg bg-canvas text-cta">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <a href="{{ route('checkout.index') }}" class="btn btn-cta w-full text-xs py-2">
                    Ödemeye Geç →
                </a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="by-card p-6 bg-surface border border-border">
            <div class="flex items-center justify-between border-b border-border pb-4 mb-4">
                <div>
                    <h2 class="font-heading text-lg font-bold text-ink">Sipariş ve Alışveriş</h2>
                    <p class="text-xs text-muted">Geçmiş siparişlerinizi ve sepet durumunuzu takip edin</p>
                </div>
                <a href="{{ route('account.orders.index') }}" class="btn btn-secondary text-xs py-1.5 px-3">Tümü</a>
            </div>

            <div class="space-y-3">
                <a href="{{ route('account.orders.index') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-border bg-canvas/40 hover:border-cta/50 hover:bg-canvas transition-colors">
                    <div>
                        <div class="text-sm font-semibold text-ink">Siparişlerim</div>
                        <div class="text-xs text-muted">Geçmiş siparişlerinizi ve kargo durumlarını görün.</div>
                    </div>
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('cart.index') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-border bg-canvas/40 hover:border-cta/50 hover:bg-canvas transition-colors">
                    <div>
                        <div class="text-sm font-semibold text-ink">Sepetim ({{ $cartCount }})</div>
                        <div class="text-xs text-muted">Eklenen ürünleri düzenleyin ve siparişi tamamlayın.</div>
                    </div>
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('customer.price-estimate') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-border bg-canvas/40 hover:border-cta/50 hover:bg-canvas transition-colors">
                    <div>
                        <div class="text-sm font-semibold text-ink">Baskı Fiyat Tahmini</div>
                        <div class="text-xs text-muted">Çoklu kalemlerle anında yaklaşık fiyat hesaplayın.</div>
                    </div>
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <div class="by-card p-6 bg-surface border border-border">
            <div class="flex items-center justify-between border-b border-border pb-4 mb-4">
                <div>
                    <h2 class="font-heading text-lg font-bold text-ink">Teklifler ve Destek</h2>
                    <p class="text-xs text-muted">Özel teklif talepleriniz ve platform destek talepleri</p>
                </div>
                <a href="{{ route('account.support.index') }}" class="btn btn-secondary text-xs py-1.5 px-3">Destek</a>
            </div>

            <div class="space-y-3">
                <a href="{{ route('quote-requests.index') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-border bg-canvas/40 hover:border-cta/50 hover:bg-canvas transition-colors">
                    <div>
                        <div class="text-sm font-semibold text-ink">Teklif Taleplerim</div>
                        <div class="text-xs text-muted">Üreticilerden gelen teklifleri inceleyin ve seçin.</div>
                    </div>
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('quote-requests.create') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-border bg-canvas/40 hover:border-cta/50 hover:bg-canvas transition-colors">
                    <div>
                        <div class="text-sm font-semibold text-ink">Yeni Teklif Talebi Aç</div>
                        <div class="text-xs text-muted">Baskı ve promosyon ihtiyaçlarınız için teklif isteyin.</div>
                    </div>
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('account.support.create') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-border bg-canvas/40 hover:border-cta/50 hover:bg-canvas transition-colors">
                    <div>
                        <div class="text-sm font-semibold text-ink">Destek Talebi Oluştur</div>
                        <div class="text-xs text-muted">Sipariş, fatura veya diğer sorularınız için iletişime geçin.</div>
                    </div>
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('freelancer-jobs.my') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-border bg-canvas/40 hover:border-cta/50 hover:bg-canvas transition-colors">
                    <div>
                        <div class="text-sm font-semibold text-ink">Hizmet İlanlarım</div>
                        <div class="text-xs text-muted">Tasarım ve hazırlık taleplerinizi yönetin.</div>
                    </div>
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
@endsection
