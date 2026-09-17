@extends('layouts.app')

@section('title', 'Güvenli Ödeme – BaskıYeri')
@section('meta_description', 'BaskıYeri 256-bit SSL korumalı güvenli ödeme sayfası.')

@section('content')
<div class="by-container py-6 sm:py-8">
    <div class="mb-6 pb-4 border-b border-[#DEDAD2]">
        <p class="text-xs font-bold uppercase tracking-wider text-[#596166] mb-0.5">Alışveriş / Güvenli Ödeme</p>
        <h1 class="text-2xl font-bold tracking-tight text-[#182023]">{{ __('ui.checkout_title') }}</h1>
        <p class="text-xs text-[#596166] mt-1">Teslimat ve fatura bilgilerinizi kontrol edin, ardından ödeme yönteminizi seçin.</p>
    </div>

    @if(!app(\App\Services\PaymentService::class)->isConfigured() || app(\App\Services\PaymentService::class)->mode() !== 'live')
        <div class="mb-5">
            <x-alert type="warning" title="Test Modu">
                Kart ödemesi test modunda. Gerçek kart bilgilerinizi kullanmayın; bu modda gerçek tahsilat yapılmaz.
            </x-alert>
        </div>
    @endif

    @if(! empty($quickBuy))
        <div class="mb-5">
            <x-alert type="info" title="Hızlı Satın Alma Oturumu">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mt-1">
                    <span>Şu anda seçtiğiniz ürün için özel satın alma oturumundasınız. Sepetinizdeki diğer ürünler korunur ve etkilenmez.</span>
                    <a href="{{ route('checkout.cancel-quick-buy') }}" class="inline-flex items-center text-xs font-bold underline shrink-0">Normal Sepete Dön</a>
                </div>
            </x-alert>
        </div>
    @endif

    @if($items->isEmpty())
        <x-empty-state 
            title="Ödeme yapılacak ürün bulunamadı" 
            message="Sepetinizde ürün bulunmuyor. Alışverişe devam etmek için ürünleri inceleyebilirsiniz."
            actionText="Sepete Dön"
            actionUrl="{{ route('cart.index') }}"
        />
    @elseif($addresses->isEmpty())
        <x-empty-state 
            title="Kayıtlı teslimat adresi bulunamadı" 
            message="Siparişi tamamlayabilmek için en az bir teslimat adresi tanımlamanız gerekmektedir."
            actionText="+ Yeni Adres Ekle"
            actionUrl="{{ route('account.adresler.create') }}"
        />
    @else
        <div class="grid gap-8 lg:grid-cols-12 items-start">
            {{-- Left Form Area (7 cols) --}}
            <div class="lg:col-span-7">
                <form action="{{ route('checkout.store') }}" method="post" id="checkout-form" class="rounded-xl border border-[#DEDAD2] bg-white p-6 shadow-xs">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                    @php($defaultBillingId = old('billing_address_id', $defaultBillingAddress?->id))

                    {{-- 1. Teslimat Adresi --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-1">
                            <h2 class="text-base font-bold text-[#182023]">1. Teslimat Adresi</h2>
                            <a href="{{ route('account.adresler.index') }}" class="text-xs font-semibold text-[#C2410C] hover:underline">Adresleri Yönet</a>
                        </div>
                        <p class="text-xs text-[#596166] mb-3">Siparişinizin teslim edileceği adresi seçin.</p>

                        <div class="space-y-2">
                            @foreach($addresses as $a)
                                <label for="shipAddr{{ $a->id }}" class="flex items-start gap-3 rounded-lg border border-[#DEDAD2] p-3.5 hover:bg-[#F7F5F0]/60 cursor-pointer transition">
                                    <input 
                                        type="radio" 
                                        name="shipping_address_id" 
                                        id="shipAddr{{ $a->id }}" 
                                        value="{{ $a->id }}" 
                                        @checked(old('shipping_address_id', $a->is_default ? $a->id : null) == $a->id) 
                                        required
                                        class="mt-1 h-4 w-4 text-[#C2410C] focus:ring-[#C2410C]"
                                    >
                                    <div class="text-xs">
                                        <p class="font-bold text-[#182023]">{{ $a->label }} <span class="font-normal text-[#596166]">({{ $a->full_name }})</span></p>
                                        <p class="text-[#596166] mt-0.5">{{ $a->formatted }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-2">
                            <a href="{{ route('account.adresler.create') }}" class="text-xs font-bold text-[#C2410C] hover:underline">+ Yeni Teslimat Adresi Ekle</a>
                        </div>
                    </div>

                    <hr class="border-[#DEDAD2] my-6">

                    {{-- 2. Fatura Adresi --}}
                    <div class="mb-6">
                        <h2 class="text-base font-bold text-[#182023] mb-2">2. Fatura Adresi</h2>
                        <label class="flex items-center gap-2 mb-3 cursor-pointer text-xs font-semibold text-[#182023]">
                            <input 
                                type="checkbox" 
                                id="use-shipping-for-billing" 
                                name="use_shipping_for_billing" 
                                value="1" 
                                @checked(old('use_shipping_for_billing', true))
                                class="h-4 w-4 rounded border-[#DEDAD2] text-[#C2410C] focus:ring-[#C2410C]"
                            >
                            <span>Teslimat adresi ile aynı olsun</span>
                        </label>

                        <input type="hidden" id="billing-address-hidden" name="billing_address_id" value="{{ $defaultBillingId }}">

                        <div id="billing-address-box" class="{{ old('use_shipping_for_billing', true) ? 'hidden' : '' }} space-y-2 mb-3">
                            @foreach($addresses as $a)
                                <label for="billAddr{{ $a->id }}" class="flex items-start gap-3 rounded-lg border border-[#DEDAD2] p-3.5 hover:bg-[#F7F5F0]/60 cursor-pointer transition">
                                    <input 
                                        type="radio" 
                                        class="billing-visible-radio mt-1 h-4 w-4 text-[#C2410C] focus:ring-[#C2410C]" 
                                        name="billing_address_pick" 
                                        id="billAddr{{ $a->id }}" 
                                        value="{{ $a->id }}" 
                                        @checked($defaultBillingId == $a->id)
                                    >
                                    <div class="text-xs">
                                        <p class="font-bold text-[#182023]">
                                            {{ $a->label }} <span class="font-normal text-[#596166]">({{ $a->full_name }})</span>
                                            @if($a->is_billing_default)<span class="ml-1 text-[10px] bg-[#F7F5F0] px-1.5 py-0.5 rounded border border-[#DEDAD2]">Varsayılan Fatura</span>@endif
                                        </p>
                                        <p class="text-[#596166] mt-0.5">{{ $a->formatted }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="rounded-lg border border-[#DEDAD2] bg-[#F7F5F0]/50 p-3 text-xs" id="selected-billing-card">
                            <span class="font-bold text-[#182023] block mb-0.5">Seçili Fatura Adresi:</span>
                            <span id="selected-billing-text" class="text-[#596166]">Teslimat adresi ile aynı.</span>
                        </div>
                    </div>

                    <hr class="border-[#DEDAD2] my-6">

                    {{-- 3. Ödeme Yöntemi --}}
                    <div class="mb-6">
                        <h2 class="text-base font-bold text-[#182023] mb-3">3. Ödeme Yöntemi</h2>
                        <div class="space-y-3">
                            <label for="payCreditCard" class="flex items-start gap-3 rounded-lg border border-[#DEDAD2] p-4 cursor-pointer hover:bg-[#F7F5F0]/60 transition">
                                <input 
                                    type="radio" 
                                    name="payment_method" 
                                    id="payCreditCard" 
                                    value="credit_card" 
                                    @checked(old('payment_method', 'credit_card') === 'credit_card')
                                    class="payment-method-radio mt-1 h-4 w-4 text-[#C2410C] focus:ring-[#C2410C]"
                                >
                                <div class="text-xs">
                                    <p class="font-bold text-[#182023] text-sm">Kredi / Banka Kartı (iyzico Güvenli Ödeme)</p>
                                    <p class="text-[#596166] mt-1 leading-relaxed">
                                        Siparişinizi onayladıktan sonra 256-bit SSL korumalı iyzico 3DS ödeme ekranına yönlendirilirsiniz. Kart bilgileriniz sunucularımızda kesinlikle saklanmaz.
                                    </p>
                                </div>
                            </label>

                            <label for="payBankTransfer" class="flex items-start gap-3 rounded-lg border border-[#DEDAD2] p-4 cursor-pointer hover:bg-[#F7F5F0]/60 transition">
                                <input 
                                    type="radio" 
                                    name="payment_method" 
                                    id="payBankTransfer" 
                                    value="bank_transfer" 
                                    @checked(old('payment_method') === 'bank_transfer')
                                    class="payment-method-radio mt-1 h-4 w-4 text-[#C2410C] focus:ring-[#C2410C]"
                                >
                                <div class="text-xs w-full">
                                    <p class="font-bold text-[#182023] text-sm">Banka Havalesi / EFT</p>
                                    <div class="payment-method-box payment-bank-transfer mt-2 {{ old('payment_method') === 'bank_transfer' ? '' : 'hidden' }}">
                                        <label class="block font-semibold text-[#182023] mb-1">Gönderim Yapacağınız IBAN / Hesap Bilgisi</label>
                                        <input type="text" name="bank_iban" class="w-full min-h-[38px] rounded-lg border border-[#DEDAD2] bg-white px-3 text-xs mb-1 outline-none" value="{{ old('bank_iban') }}" placeholder="TR..">
                                        <p class="text-[11px] text-[#596166]">Havale/EFT ile ödemelerde siparişiniz onay bekliyor durumunda kaydedilir ve yönetici onayı sonrasında üretime alınır.</p>
                                    </div>
                                </div>
                            </label>

                            <label for="payCashOnDelivery" class="flex items-start gap-3 rounded-lg border border-[#DEDAD2] p-4 cursor-pointer hover:bg-[#F7F5F0]/60 transition">
                                <input 
                                    type="radio" 
                                    name="payment_method" 
                                    id="payCashOnDelivery" 
                                    value="cash_on_delivery" 
                                    @checked(old('payment_method') === 'cash_on_delivery')
                                    class="payment-method-radio mt-1 h-4 w-4 text-[#C2410C] focus:ring-[#C2410C]"
                                >
                                <div class="text-xs">
                                    <p class="font-bold text-[#182023] text-sm">Kapıda Ödeme</p>
                                    <p class="text-[#596166] mt-0.5">Siparişiniz üretici onayı bekliyor durumunda oluşturulur.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <hr class="border-[#DEDAD2] my-6">

                    {{-- 4. Fatura Bilgileri --}}
                    <div class="mb-6">
                        <h2 class="text-base font-bold text-[#182023] mb-3">4. Fatura Bilgileri</h2>
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-[#182023] mb-1">Fatura Tipi</label>
                            <select name="invoice_type" id="invoice-type" class="w-full min-h-[44px] rounded-lg border border-[#DEDAD2] bg-white px-3 text-sm text-[#182023] outline-none" required>
                                <option value="individual" @selected(old('invoice_type', $billingProfile->invoice_type ?? 'individual') === 'individual')>Bireysel Fatura</option>
                                <option value="corporate" @selected(old('invoice_type', $billingProfile->invoice_type ?? 'individual') === 'corporate')>Kurumsal Fatura</option>
                            </select>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 mb-3">
                            <div>
                                <label class="block text-xs font-semibold text-[#182023] mb-1">Ad Soyad</label>
                                <input type="text" name="invoice_full_name" class="w-full min-h-[40px] rounded-lg border border-[#DEDAD2] px-3 text-sm text-[#182023] outline-none" value="{{ old('invoice_full_name', $billingProfile->full_name ?? auth()->user()->name) }}" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#182023] mb-1">Telefon</label>
                                <input type="text" name="invoice_phone" class="w-full min-h-[40px] rounded-lg border border-[#DEDAD2] px-3 text-sm text-[#182023] outline-none" value="{{ old('invoice_phone', $billingProfile->phone ?? '') }}" required>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 mb-3">
                            <div>
                                <label class="block text-xs font-semibold text-[#182023] mb-1">E-posta</label>
                                <input type="email" name="invoice_email" class="w-full min-h-[40px] rounded-lg border border-[#DEDAD2] px-3 text-sm text-[#182023] outline-none" value="{{ old('invoice_email', $billingProfile->email ?? auth()->user()->email) }}" required>
                            </div>
                            <div class="individual-only">
                                <label class="block text-xs font-semibold text-[#182023] mb-1">TCKN (isteğe bağlı)</label>
                                <input type="text" name="invoice_identity_number" class="w-full min-h-[40px] rounded-lg border border-[#DEDAD2] px-3 text-sm text-[#182023] outline-none" value="{{ old('invoice_identity_number', $billingProfile->identity_number ?? '') }}" maxlength="16">
                            </div>
                        </div>

                        <div class="corporate-only {{ old('invoice_type', $billingProfile->invoice_type ?? 'individual') === 'corporate' ? '' : 'hidden' }} space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-[#182023] mb-1">Şirket Unvanı</label>
                                <input type="text" name="invoice_company_name" class="w-full min-h-[40px] rounded-lg border border-[#DEDAD2] px-3 text-sm text-[#182023] outline-none" value="{{ old('invoice_company_name', $billingProfile->company_name ?? '') }}">
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-semibold text-[#182023] mb-1">Vergi Numarası (VKN)</label>
                                    <input type="text" name="invoice_tax_number" class="w-full min-h-[40px] rounded-lg border border-[#DEDAD2] px-3 text-sm text-[#182023] outline-none" value="{{ old('invoice_tax_number', $billingProfile->tax_number ?? '') }}" maxlength="16">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-[#182023] mb-1">Vergi Dairesi</label>
                                    <input type="text" name="invoice_tax_office" class="w-full min-h-[40px] rounded-lg border border-[#DEDAD2] px-3 text-sm text-[#182023] outline-none" value="{{ old('invoice_tax_office', $billingProfile->tax_office ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-[#DEDAD2] my-6">

                    {{-- 5. Mesafeli Satış Sözleşmesi Onayı --}}
                    <div class="rounded-lg border border-[#DEDAD2] bg-[#F7F5F0]/60 p-4 text-xs mb-6">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="accept-distance-sales" 
                                name="accept_distance_sales" 
                                value="1" 
                                disabled
                                class="mt-0.5 h-4 w-4 rounded border-[#DEDAD2] text-[#C2410C] focus:ring-[#C2410C]"
                            >
                            <div>
                                <span><strong>Mesafeli Satış Sözleşmesi</strong>'ni okudum ve kabul ediyorum.</span>
                                <button type="button" class="font-bold text-[#C2410C] underline ml-1 cursor-pointer" id="open-distance-sales">
                                    Sözleşmeyi Oku ve Onayla
                                </button>
                                <p class="text-[11px] text-[#596166] mt-1">Sözleşmeyi sonuna kadar kaydırdıktan sonra onay kutusu aktifleşir.</p>
                            </div>
                        </label>
                        <input type="hidden" name="contract_scrolled_at" id="contract_scrolled_at" value="">
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" id="checkout-submit" disabled class="w-full min-h-[48px] rounded-lg bg-[#C2410C] text-sm font-bold text-white shadow-xs hover:bg-[#9A3412] disabled:opacity-50 disabled:cursor-not-allowed transition">
                        Siparişi Tamamla →
                    </button>
                </form>

                {{-- Contract Modal --}}
                <div id="distanceSalesModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-[#182023]/60 backdrop-blur-xs p-4">
                    <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white border border-[#DEDAD2] shadow-2xl">
                        <div class="flex items-center justify-between border-b border-[#DEDAD2] px-6 py-4">
                            <h3 class="text-base font-bold text-[#182023]">Mesafeli Satış Sözleşmesi</h3>
                            <button type="button" id="close-distance-sales" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#596166] hover:bg-[#F7F5F0]">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-6 text-xs text-[#596166] leading-relaxed" id="distance-sales-body" style="max-height: 55vh;">
                            {!! $distanceSalesContract?->content_html ?? '<p>Sözleşme metni henüz tanımlanmadı. Lütfen yönetici panelinden ekleyin.</p>' !!}
                        </div>
                        <div class="flex justify-end gap-2 border-t border-[#DEDAD2] px-6 py-3.5 bg-[#F7F5F0]/50">
                            <button type="button" class="inline-flex min-h-[40px] items-center rounded-lg bg-[#C2410C] px-5 text-xs font-bold text-white disabled:opacity-50 disabled:cursor-not-allowed transition" id="confirm-distance-sales" disabled>
                                Okudum, Onaylıyorum
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Summary Area (5 cols) --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="rounded-xl border border-[#DEDAD2] bg-white p-6 shadow-xs">
                    <h2 class="text-base font-bold text-[#182023] mb-4 pb-2 border-b border-[#DEDAD2]">Sipariş Özeti</h2>
                    <ul class="divide-y divide-[#DEDAD2]/70 text-xs">
                        @foreach($items as $item)
                            @php $unit = (float) $item->product->price + (float) ($item->variant?->price_adjustment ?? 0); @endphp
                            <li class="py-2.5 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-[#182023] truncate">{{ $item->product->name }}</p>
                                    @if($item->variant)
                                        <p class="text-[11px] text-[#596166]">{{ $item->variant->name }}</p>
                                    @endif
                                    <span class="text-[11px] text-[#596166]">{{ $item->quantity }} adet</span>
                                </div>
                                <span class="font-bold text-sm text-[#182023] shrink-0">
                                    ₺{{ number_format(bcmul((string) $unit, (string) $item->quantity, 2), 2, ',', '.') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 pt-4 border-t border-[#DEDAD2] flex items-baseline justify-between">
                        <span class="font-bold text-sm text-[#182023]">Toplam Tutar</span>
                        <span class="text-xl font-extrabold text-[#C2410C]">₺{{ number_format($total, 2, ',', '.') }}</span>
                    </div>
                </div>

                <div class="rounded-xl border border-[#DEDAD2] bg-white p-5 shadow-xs text-xs text-[#596166] space-y-2">
                    <p class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Ödemeniz 256-bit SSL güvencesindedir.</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Siparişiniz onaylanmadan kartınızdan para çekilmez.</span>
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const shippingRadios = Array.from(document.querySelectorAll('input[name="shipping_address_id"]'));
        const hiddenBillingInput = document.getElementById('billing-address-hidden');
        const useShippingCheckbox = document.getElementById('use-shipping-for-billing');
        if (!useShippingCheckbox) return;
        const billingBox = document.getElementById('billing-address-box');
        const billingVisibleRadios = Array.from(document.querySelectorAll('.billing-visible-radio'));
        const invoiceTypeSelect = document.getElementById('invoice-type');
        const corporateFields = document.querySelectorAll('.corporate-only');
        const selectedBillingText = document.getElementById('selected-billing-text');
        const paymentMethodRadios = Array.from(document.querySelectorAll('.payment-method-radio'));
        const paymentBankTransferBox = document.querySelector('.payment-bank-transfer');

        function syncBillingFromShipping() {
            if (!useShippingCheckbox.checked) return;
            const selected = shippingRadios.find((input) => input.checked);
            if (!selected) return;
            hiddenBillingInput.value = selected.value;
            const label = document.querySelector('label[for="' + selected.id + '"]');
            if (selectedBillingText && label) selectedBillingText.textContent = label.textContent.trim();
        }

        function toggleBillingBox() {
            const sameAddress = useShippingCheckbox.checked;
            billingBox.classList.toggle('hidden', sameAddress);
            if (sameAddress) {
                syncBillingFromShipping();
            } else {
                const selected = billingVisibleRadios.find((input) => input.checked);
                hiddenBillingInput.value = selected ? selected.value : (hiddenBillingInput.value || '');
                if (selected && selectedBillingText) {
                    const label = document.querySelector('label[for="' + selected.id + '"]');
                    if (label) selectedBillingText.textContent = label.textContent.trim();
                }
            }
        }
        billingVisibleRadios.forEach((input) => input.addEventListener('change', function () {
            if (!useShippingCheckbox.checked && input.checked) {
                hiddenBillingInput.value = input.value;
                if (selectedBillingText) {
                    const label = document.querySelector('label[for="' + input.id + '"]');
                    if (label) selectedBillingText.textContent = label.textContent.trim();
                }
            }
        }));

        function toggleInvoiceType() {
            const isCorporate = invoiceTypeSelect.value === 'corporate';
            corporateFields.forEach((el) => el.classList.toggle('hidden', !isCorporate));
        }
        function togglePaymentMethod() {
            const selected = paymentMethodRadios.find((radio) => radio.checked);
            const method = selected ? selected.value : 'credit_card';
            if (paymentBankTransferBox) paymentBankTransferBox.classList.toggle('hidden', method !== 'bank_transfer');
        }

        shippingRadios.forEach((input) => input.addEventListener('change', syncBillingFromShipping));
        useShippingCheckbox.addEventListener('change', toggleBillingBox);
        invoiceTypeSelect.addEventListener('change', toggleInvoiceType);
        paymentMethodRadios.forEach((radio) => radio.addEventListener('change', togglePaymentMethod));
        toggleBillingBox();
        toggleInvoiceType();
        togglePaymentMethod();

        const distanceCheckbox = document.getElementById('accept-distance-sales');
        const openDistanceBtn = document.getElementById('open-distance-sales');
        const confirmDistanceBtn = document.getElementById('confirm-distance-sales');
        const distanceBody = document.getElementById('distance-sales-body');
        const checkoutSubmit = document.getElementById('checkout-submit');
        const scrolledAtInput = document.getElementById('contract_scrolled_at');
        const modalEl = document.getElementById('distanceSalesModal');
        const closeDistanceBtn = document.getElementById('close-distance-sales');
        let scrolledToEnd = false;

        function openModal() {
            if (!modalEl) return;
            modalEl.classList.remove('hidden');
            modalEl.classList.add('flex');
            checkContractScroll();
        }
        function closeModal() {
            if (!modalEl) return;
            modalEl.classList.add('hidden');
            modalEl.classList.remove('flex');
        }
        function syncCheckoutSubmit() {
            if (checkoutSubmit) checkoutSubmit.disabled = !(distanceCheckbox && distanceCheckbox.checked);
        }

        function checkContractScroll() {
            if (distanceBody) {
                if (distanceBody.scrollTop + distanceBody.clientHeight >= distanceBody.scrollHeight - 8) {
                    scrolledToEnd = true;
                    if (confirmDistanceBtn) confirmDistanceBtn.disabled = false;
                    if (scrolledAtInput && !scrolledAtInput.value) scrolledAtInput.value = new Date().toISOString();
                }
            }
        }
        distanceBody?.addEventListener('scroll', checkContractScroll);
        openDistanceBtn?.addEventListener('click', function (e) {
            e.preventDefault();
            openModal();
        });
        closeDistanceBtn?.addEventListener('click', closeModal);
        distanceCheckbox?.addEventListener('click', function (e) {
            if (!distanceCheckbox.checked && !scrolledToEnd) {
                e.preventDefault();
                openModal();
            }
            syncCheckoutSubmit();
        });
        confirmDistanceBtn?.addEventListener('click', function () {
            if (!scrolledToEnd) return;
            distanceCheckbox.disabled = false;
            distanceCheckbox.checked = true;
            syncCheckoutSubmit();
            closeModal();
        });
        syncCheckoutSubmit();

        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function () {
                if (checkoutSubmit && !checkoutSubmit.disabled) {
                    checkoutSubmit.disabled = true;
                    checkoutSubmit.innerText = 'İşleniyor, lütfen bekleyin...';
                }
            });
        }
    });
</script>
@endsection
