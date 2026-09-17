@extends('layouts.account')

@section('title', 'Hesabım')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Hesabım</p>
            <h1 class="mt-1 text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900">Merhaba, {{ $user->name }}</h1>
            <p class="mt-2 text-sm text-slate-600">Siparişlerin, teklifler ve hesabınla ilgili her şey burada.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('products.index') }}" class="by-btn-secondary">Alışveriş</a>
            <a href="{{ route('outdoor.index') }}" class="by-btn-secondary">Açık hava</a>
            <a href="{{ route('quote-requests.create') }}" class="by-btn-primary">+ Teklif talebi</a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="by-surface-amber">
            <div class="by-accent-bar mb-3"></div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600">Sipariş</div>
            <div class="mt-2 flex items-end justify-between gap-3">
                <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $ordersCount }}</div>
                <a href="{{ route('account.orders.index') }}" class="text-sm font-semibold text-slate-700 hover:underline">Görüntüle</a>
            </div>
        </div>

        <div class="by-surface-cyan">
            <div class="by-accent-bar mb-3"></div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600">Sepet</div>
            <div class="mt-2 flex items-end justify-between gap-3">
                <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $cartCount }}</div>
                <a href="{{ route('cart.index') }}" class="text-sm font-semibold text-slate-700 hover:underline">Sepete git</a>
            </div>
        </div>

        <div class="by-surface-indigo">
            <div class="by-accent-bar mb-3"></div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600">Favori</div>
            <div class="mt-2 flex items-end justify-between gap-3">
                <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $favoritesCount }}</div>
                <a href="{{ route('favorites.index') }}" class="text-sm font-semibold text-slate-700 hover:underline">Aç</a>
            </div>
        </div>

        <a href="{{ route('checkout.index') }}" class="by-surface by-card-hover block">
            <div class="by-accent-bar mb-3"></div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600">Hızlı işlem</div>
            <div class="mt-2 flex items-center justify-between gap-3">
                <div class="text-base font-extrabold tracking-tight text-slate-900">Ödemeye geç</div>
                <span class="by-btn-cta px-4 py-2">Devam</span>
            </div>
            <p class="mt-2 text-sm text-slate-600">Sepetindeki ürünleri hızlıca tamamla.</p>
        </a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="by-card p-5">
            <div class="by-section-head">
                <div>
                    <p class="by-section-title text-slate-500">Sipariş &amp; ödeme</p>
                    <h2 class="mt-1 text-lg font-extrabold tracking-tight">Alışveriş akışın</h2>
                </div>
                <a href="{{ route('account.orders.index') }}" class="by-btn-secondary">Siparişler</a>
            </div>
            <div class="by-divider my-4"></div>
            <div class="grid gap-2">
                <a href="{{ route('account.orders.index') }}" class="by-card by-card-hover p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-slate-900">Siparişlerim</div>
                            <div class="text-sm text-slate-600">Geçmiş siparişleri ve durumlarını gör.</div>
                        </div>
                        <span class="text-slate-400">→</span>
                    </div>
                </a>
                <a href="{{ route('cart.index') }}" class="by-card by-card-hover p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-slate-900">Sepetim</div>
                            <div class="text-sm text-slate-600">Ürünleri düzenle ve toplamı kontrol et.</div>
                        </div>
                        <span class="text-slate-400">→</span>
                    </div>
                </a>
                <a href="{{ route('customer.price-estimate') }}" class="by-card by-card-hover p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-slate-900">İş hesaplama</div>
                            <div class="text-sm text-slate-600">Çoklu kalemle hızlı fiyat tahmini al.</div>
                        </div>
                        <span class="text-slate-400">→</span>
                    </div>
                </a>
            </div>
        </div>

        <div class="by-card p-5">
            <div class="by-section-head">
                <div>
                    <p class="by-section-title text-slate-500">Talepler &amp; destek</p>
                    <h2 class="mt-1 text-lg font-extrabold tracking-tight">Teklif ve destek akışın</h2>
                </div>
                <a href="{{ route('account.support.index') }}" class="by-btn-secondary">Destek</a>
            </div>
            <div class="by-divider my-4"></div>
            <div class="grid gap-2">
                <a href="{{ route('quote-requests.index') }}" class="by-card by-card-hover p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-slate-900">Teklif taleplerim</div>
                            <div class="text-sm text-slate-600">Gelen teklifleri karşılaştır.</div>
                        </div>
                        <span class="text-slate-400">→</span>
                    </div>
                </a>
                <a href="{{ route('quote-requests.create') }}" class="by-card by-card-hover p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-slate-900">Yeni teklif talebi</div>
                            <div class="text-sm text-slate-600">Kalem kalem ürün/hizmet ekle.</div>
                        </div>
                        <span class="text-slate-400">→</span>
                    </div>
                </a>
                <a href="{{ route('account.support.create') }}" class="by-card by-card-hover p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-slate-900">Yeni destek talebi</div>
                            <div class="text-sm text-slate-600">Sipariş veya platform sorularını ilet.</div>
                        </div>
                        <span class="text-slate-400">→</span>
                    </div>
                </a>
                <a href="{{ route('freelancer-jobs.my') }}" class="by-card by-card-hover p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-slate-900">İlanlarım</div>
                            <div class="text-sm text-slate-600">Freelancer iş ilanlarını yönet.</div>
                        </div>
                        <span class="text-slate-400">→</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
