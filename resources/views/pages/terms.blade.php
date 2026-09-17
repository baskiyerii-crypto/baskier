@extends('layouts.app')

@section('title', 'Kullanım Koşulları - BaskıYeri')

@section('content')
<div class="by-container py-12 md:py-16">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="font-heading text-3xl md:text-4xl font-bold tracking-tight text-ink">Kullanım Koşulları</h1>
            <p class="text-xs text-muted mt-1">Son güncelleme: {{ date('d.m.Y') }}</p>
        </div>

        <div class="by-card p-6 md:p-10 bg-surface border border-border text-xs text-ink leading-relaxed space-y-4">
            <p>
                BaskıYeri bir pazaryeri platformudur. Satıcılar ile müşteriler arasındaki ürün satışı, baskı üretimi ve tasarım hizmetlerinde taraflar kendi akdi sorumluluklarındadır.
            </p>
            <h2 class="font-heading text-sm font-bold text-ink pt-2">1. Sipariş ve Üretim Süreci</h2>
            <p>
                Özel baskılı siparişlerde üretici tarafından yüklenen dijital prova müşteri tarafından onaylandıktan sonra baskı aşamasına geçilir. Onaylanan çalışmalardaki yazım veya yerleşim hatalarından platform sorumlu tutulamaz.
            </p>
            <h2 class="font-heading text-sm font-bold text-ink pt-2">2. İptal ve İade Şartları</h2>
            <p>
                Kişiye ve kuruma özel üretilen baskılı materyallerde (Mesafeli Sözleşmeler Yönetmeliği m. 15/b uyarınca) cayma hakkı bulunmamaktadır. Ayıplı veya hatalı üretim durumunda satıcı yeniden baskı veya iade ile yükümlüdür.
            </p>
            <h2 class="font-heading text-sm font-bold text-ink pt-2">3. Hesap Güvenliği</h2>
            <p class="mb-0">
                Kullanıcılar hesap erişim şifrelerinin güvenliğinden kendileri sorumludur. Koşullar güncellenebilir ve güncel metin platform üzerinden duyurulur.
            </p>
        </div>
    </div>
</div>
@endsection
