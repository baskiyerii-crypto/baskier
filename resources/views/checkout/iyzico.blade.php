@extends('layouts.app')

@section('title', 'Güvenli Kart ile Ödeme – iyzico')
@section('meta_description', 'BaskıYeri 256-bit SSL korumalı iyzico güvenli kart ile ödeme ekranı.')

@section('content')
<div class="by-container py-8 sm:py-12">
    <div class="max-w-2xl mx-auto rounded-xl border border-[#DEDAD2] bg-white p-6 sm:p-10 shadow-xs">
        <div class="text-center mb-6 pb-4 border-b border-[#DEDAD2]">
            <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-[#C2410C] mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                256-Bit SSL Korumalı Ödeme
            </span>
            <h1 class="text-2xl font-extrabold text-[#182023] tracking-tight">Güvenli Kart ile Ödeme</h1>
            <p class="text-xs text-[#596166] mt-1.5 max-w-md mx-auto">
                iyzico güvenli ödeme formu üzerinden kart bilgilerinizi girerek siparişinizi onaylayabilirsiniz.
            </p>
        </div>

        <div id="iyzipay-checkout-form" class="responsive min-h-[400px]">
            {!! $checkoutFormContent !!}
        </div>

        <div class="mt-8 pt-6 border-t border-[#DEDAD2] text-center text-xs text-[#596166]">
            <p>Kart bilgileriniz sunucularımızda saklanmaz. Ödeme iyzico güvenli sunucuları üzerinde gerçekleşmektedir.</p>
            <a href="{{ route('checkout.index') }}" class="inline-flex items-center gap-1 mt-3 font-semibold text-[#C2410C] hover:underline">
                ← Ödeme Seçeneklerine Dön
            </a>
        </div>
    </div>
</div>
@endsection
