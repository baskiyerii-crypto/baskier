@extends('layouts.app')

@section('title', 'Hakkımızda - BaskıYeri')

@section('content')
<div class="by-container py-12 md:py-16">
    <div class="max-w-3xl mx-auto">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-wider text-muted mb-1">Biz Kimiz?</p>
            <h1 class="font-heading text-3xl md:text-4xl font-bold tracking-tight text-ink">Baskı ve Tasarımın Dijital Buluşma Noktası</h1>
        </div>

        <div class="by-card p-6 md:p-10 bg-surface border border-border space-y-6 text-sm text-ink leading-relaxed">
            <p>
                <strong>BaskıYeri</strong>; matbaa, dijital baskı, açık hava reklamcılığı, tabela, promosyon ve freelance grafik tasarım hizmetlerinin tek çatı altında buluştuğu, Türkiye'nin ilk odaklı çok satıcılı baskı pazaryeridir.
            </p>
            <p>
                Müşteriler yüzlerce onaylı atölyenin hazır vitrin ürünlerini inceleyip anında sipariş verebilir; özel ve karmaşık projeleri için kalem kalem teklif talebi toplayarak en rekabetçi üreticiyle eşleşebilir.
            </p>
            <div class="p-5 rounded-xl bg-canvas/60 border border-border">
                <h2 class="font-heading text-base font-bold text-ink mb-2">Güven ve Kalite Taahhüdümüz</h2>
                <p class="text-xs text-muted leading-relaxed mb-0">
                    Sistemimizde yer alan tüm üreticiler vergi levhası ve resmi evrak denetiminden geçer. Özel baskılı siparişlerde dijital prova onayı alınmadan üretime geçilmez; böylece hatalı basım riski sıfıra indirilir.
                </p>
            </div>
            <p class="text-xs text-muted pt-4 border-t border-border mb-0">
                Her türlü soru ve iş birliği için bize <a href="mailto:info@baskiyeri.com" class="font-bold text-cta hover:underline">info@baskiyeri.com</a> adresinden ulaşabilirsiniz.
            </p>
        </div>
    </div>
</div>
@endsection
