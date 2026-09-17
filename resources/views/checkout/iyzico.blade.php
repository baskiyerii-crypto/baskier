@extends('layouts.app')

@section('title', 'Güvenli Ödeme - iyzico')

@section('content')
<div class="by-container py-10">
    <div class="max-w-2xl mx-auto by-card p-6 sm:p-10">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-slate-900">Güvenli Kart ile Ödeme</h1>
            <p class="text-sm text-slate-600 mt-2">iyzico 256-bit SSL korumalı altyapısı ile kart bilgilerinizi güvenle girerek ödemenizi tamamlayın.</p>
        </div>

        <div id="iyzipay-checkout-form" class="responsive min-h-[400px]">
            {!! $checkoutFormContent !!}
        </div>

        <div class="mt-8 pt-6 border-t border-slate-200 text-center text-xs text-slate-500">
            <p>Kart bilgileriniz sistemimizde saklanmaz. Ödeme iyzico güvenli sunucuları üzerinde gerçekleşmektedir.</p>
            <a href="{{ route('checkout.index') }}" class="inline-block mt-3 text-indigo-600 font-semibold hover:underline">← Ödeme Seçeneklerine Dön</a>
        </div>
    </div>
</div>
@endsection
