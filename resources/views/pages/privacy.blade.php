@extends('layouts.app')

@section('title', 'Gizlilik Politikası - BaskıYeri')

@section('content')
<div class="by-container py-12 md:py-16">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="font-heading text-3xl md:text-4xl font-bold tracking-tight text-ink">Gizlilik Politikası ve KVKK Aydınlatma Metni</h1>
            <p class="text-xs text-muted mt-1">Son güncelleme: {{ date('d.m.Y') }}</p>
        </div>

        <div class="by-card p-6 md:p-10 bg-surface border border-border text-xs text-ink leading-relaxed space-y-4">
            <p>
                BaskıYeri olarak kişisel verilerinizi 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında en yüksek güvenlik önlemleriyle koruyoruz.
            </p>
            <h2 class="font-heading text-sm font-bold text-ink pt-2">1. İşlenen Kişisel Veriler</h2>
            <p>
                Platformumuza kayıt olduğunuzda veya sipariş oluşturduğunuzda; adınız, soyadınız, teslimat ve fatura adresiniz, telefon numaranız, e-posta adresiniz ve ödeme işlem referansları işlenmektedir.
            </p>
            <h2 class="font-heading text-sm font-bold text-ink pt-2">2. Verilerin Paylaşımı</h2>
            <p>
                Verileriniz siparişin teslim edilmesi amacıyla kargo şirketleriyle, özel teklif süreçlerinde açık onay verdiğiniz üreticilerle ve kanunen yetkili kamu kurumlarıyla paylaşılabilir.
            </p>
            <h2 class="font-heading text-sm font-bold text-ink pt-2">3. İletişim ve Haklarınız</h2>
            <p>
                KVKK 11. maddesi kapsamındaki tüm haklarınız için <a href="mailto:info@baskiyeri.com" class="text-cta font-semibold hover:underline">info@baskiyeri.com</a> üzerinden taleplerinizi iletebilirsiniz.
            </p>
        </div>
    </div>
</div>
@endsection
