@extends('layouts.app')

@section('title', 'Ödeme - BaskıYeri')

@section('content')
<div class="content-shell py-4">
    <h1 class="h5 mb-4">Ödeme</h1>
    <p class="small text-muted">Kart ödemesi iyzico üzerinden işlenir. API anahtarları tanımlı değilse sandbox/demo kaydı oluşur.</p>

    @if($items->isEmpty())
        <p><a href="{{ route('cart.index') }}">Sepete dön</a></p>
    @elseif($addresses->isEmpty())
        <div class="alert alert-warning">Önce <a href="{{ route('account.adresler.create') }}">bir teslimat adresi ekleyin</a>.</div>
    @else
        <div class="row g-4">
            <div class="col-lg-7">
                <form action="{{ route('checkout.store') }}" method="post" class="bg-white rounded-4 shadow-sm p-4">
                    @csrf
                    @php($defaultBillingId = old('billing_address_id', $defaultBillingAddress?->id))
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 mb-0">Teslimat adresi</h2>
                        <a href="{{ route('account.adresler.index') }}" class="small">Adres adlarını düzenle</a>
                    </div>
                    <p class="small text-muted mb-3">Adres adı olarak Ev, İş, Ofis gibi etiketler kullanabilirsiniz.</p>
                    @foreach($addresses as $a)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="shipping_address_id" id="shipAddr{{ $a->id }}" value="{{ $a->id }}" @checked(old('shipping_address_id', $a->is_default ? $a->id : null) == $a->id) required>
                            <label class="form-check-label" for="shipAddr{{ $a->id }}">
                                <strong>{{ $a->label }}</strong> — {{ $a->full_name }}, {{ $a->formatted }}
                            </label>
                        </div>
                    @endforeach
                    <a href="{{ route('account.adresler.create') }}" class="small">+ Yeni adres</a>
                    <hr>
                    <h2 class="h6 mb-3">Fatura adresi</h2>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="use-shipping-for-billing" name="use_shipping_for_billing" value="1" @checked(old('use_shipping_for_billing', true))>
                        <label class="form-check-label" for="use-shipping-for-billing">Teslimat adresi ile aynı olsun</label>
                    </div>
                    <input type="hidden" id="billing-address-hidden" name="billing_address_id" value="{{ $defaultBillingId }}">
                    <div id="billing-address-box" class="{{ old('use_shipping_for_billing', true) ? 'd-none' : '' }}">
                        @foreach($addresses as $a)
                            <div class="form-check mb-2">
                                <input class="form-check-input billing-visible-radio" type="radio" name="billing_address_pick" id="billAddr{{ $a->id }}" value="{{ $a->id }}" @checked($defaultBillingId == $a->id)>
                                <label class="form-check-label" for="billAddr{{ $a->id }}">
                                    <strong>{{ $a->label }}</strong> — {{ $a->full_name }}, {{ $a->formatted }}
                                    @if($a->is_billing_default)<span class="badge bg-secondary ms-1">Varsayılan fatura</span>@endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="bg-light rounded-3 p-2 small mb-3" id="selected-billing-card">
                        <div class="fw-semibold mb-1">Seçili fatura adresi</div>
                        <div id="selected-billing-text" class="text-muted">Teslimat adresi ile aynı.</div>
                    </div>
                    <hr>
                    <h2 class="h6 mb-3">Ödeme yöntemi</h2>
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="payCreditCard" value="credit_card" @checked(old('payment_method', 'credit_card') === 'credit_card')>
                            <label class="form-check-label" for="payCreditCard">Kredi / Banka Kartı</label>
                        </div>
                        <div class="payment-method-box payment-credit-card {{ old('payment_method', 'credit_card') === 'credit_card' ? '' : 'd-none' }}">
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Kart üzerindeki isim</label>
                                    <input type="text" name="card_holder_name" class="form-control" value="{{ old('card_holder_name', auth()->user()->name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kart numarası</label>
                                    <input type="text" name="card_number" class="form-control" value="{{ old('card_number') }}" placeholder="**** **** **** ****">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Son kullanma</label>
                                    <input type="text" name="card_expiry" class="form-control" value="{{ old('card_expiry') }}" placeholder="AA/YY">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">CVC</label>
                                    <input type="text" name="card_cvc" class="form-control" value="{{ old('card_cvc') }}" placeholder="***">
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-2 mt-3">
                            <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="payBankTransfer" value="bank_transfer" @checked(old('payment_method') === 'bank_transfer')>
                            <label class="form-check-label" for="payBankTransfer">Banka Havalesi / EFT</label>
                        </div>
                        <div class="payment-method-box payment-bank-transfer {{ old('payment_method') === 'bank_transfer' ? '' : 'd-none' }}">
                            <label class="form-label">Gönderim yapılacak IBAN</label>
                            <input type="text" name="bank_iban" class="form-control mb-2" value="{{ old('bank_iban') }}" placeholder="TR..">
                            <p class="small text-muted mb-0">Demo mod: Havale seçimi siparişi oluşturur, manuel kontrol varsayılır.</p>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="payCashOnDelivery" value="cash_on_delivery" @checked(old('payment_method') === 'cash_on_delivery')>
                            <label class="form-check-label" for="payCashOnDelivery">Kapıda Ödeme</label>
                        </div>
                    </div>
                    <hr>
                    <h2 class="h6 mb-3">Fatura bilgileri</h2>
                    <div class="mb-3">
                        <label class="form-label">Fatura tipi</label>
                        <select class="form-select" name="invoice_type" id="invoice-type" required>
                            <option value="individual" @selected(old('invoice_type', $billingProfile->invoice_type ?? 'individual') === 'individual')>Bireysel</option>
                            <option value="corporate" @selected(old('invoice_type', $billingProfile->invoice_type ?? 'individual') === 'corporate')>Kurumsal</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" name="invoice_full_name" class="form-control" value="{{ old('invoice_full_name', $billingProfile->full_name ?? auth()->user()->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="invoice_phone" class="form-control" value="{{ old('invoice_phone', $billingProfile->phone ?? '') }}" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="invoice_email" class="form-control" value="{{ old('invoice_email', $billingProfile->email ?? auth()->user()->email) }}" required>
                        </div>
                        <div class="col-md-6 individual-only">
                            <label class="form-label">TCKN (isteğe bağlı)</label>
                            <input type="text" name="invoice_identity_number" class="form-control" value="{{ old('invoice_identity_number', $billingProfile->identity_number ?? '') }}" maxlength="16">
                        </div>
                    </div>
                    <div class="corporate-only {{ old('invoice_type', $billingProfile->invoice_type ?? 'individual') === 'corporate' ? '' : 'd-none' }}">
                        <div class="row g-2 mb-2">
                            <div class="col-md-12">
                                <label class="form-label">Şirket unvanı</label>
                                <input type="text" name="invoice_company_name" class="form-control" value="{{ old('invoice_company_name', $billingProfile->company_name ?? '') }}">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label">VKN</label>
                                <input type="text" name="invoice_tax_number" class="form-control" value="{{ old('invoice_tax_number', $billingProfile->tax_number ?? '') }}" maxlength="16">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vergi dairesi</label>
                                <input type="text" name="invoice_tax_office" class="form-control" value="{{ old('invoice_tax_office', $billingProfile->tax_office ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="bg-light rounded-3 p-3 small mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="accept-distance-sales" name="accept_distance_sales" value="1" disabled>
                            <label class="form-check-label" for="accept-distance-sales">
                                <strong>Mesafeli Satış Sözleşmesi</strong>'ni okudum ve kabul ediyorum.
                                <button type="button" class="btn btn-link btn-sm p-0 align-baseline" id="open-distance-sales">Sözleşmeyi aç</button>
                            </label>
                        </div>
                        <input type="hidden" name="contract_scrolled_at" id="contract_scrolled_at" value="">
                        <div class="form-text">Onay kutusu, sözleşmeyi sonuna kadar okuduktan sonra aktif olur.</div>
                    </div>
                    <button type="submit" class="btn btn-warning rounded-pill px-5" id="checkout-submit" disabled>Siparişi tamamla</button>
                </form>

                <div id="distanceSalesModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
                    <div class="flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b px-4 py-3">
                            <h5 class="m-0 text-base font-semibold">Mesafeli Satış Sözleşmesi</h5>
                            <button type="button" id="close-distance-sales" class="btn btn-sm btn-outline-secondary">Kapat</button>
                        </div>
                        <div class="flex-1 overflow-auto p-4" id="distance-sales-body" style="max-height:55vh;">
                            {!! $distanceSalesContract?->content_html ?? '<p>Sözleşme metni henüz tanımlanmadı. Lütfen yönetici panelinden ekleyin.</p>' !!}
                        </div>
                        <div class="flex justify-end gap-2 border-t px-4 py-3">
                            <button type="button" class="btn btn-primary" id="confirm-distance-sales" disabled>Okudum, onayla</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="bg-white rounded-4 shadow-sm p-4">
                    <h2 class="h6 mb-3">Özet</h2>
                    <ul class="list-unstyled small mb-0">
                        <?php foreach ($items as $item) { ?>
                            <?php $unit = (float) $item->product->price + (float) ($item->variant?->price_adjustment ?? 0); ?>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span>
                                    {{ Str::limit($item->product->name, 32) }}
                                    @if($item->variant)
                                        ({{ $item->variant->name }})
                                    @endif
                                    × {{ $item->quantity }}
                                </span>
                                <span>₺{{ number_format(bcmul((string) $unit, (string) $item->quantity, 2), 2, ',', '.') }}</span>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="d-flex justify-content-between mt-3 fw-bold">
                        <span>Toplam</span>
                        <span>₺{{ number_format($total, 2, ',', '.') }}</span>
                    </div>
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
        const billingBox = document.getElementById('billing-address-box');
        const billingVisibleRadios = Array.from(document.querySelectorAll('.billing-visible-radio'));
        const invoiceTypeSelect = document.getElementById('invoice-type');
        const corporateFields = document.querySelectorAll('.corporate-only');
        const selectedBillingText = document.getElementById('selected-billing-text');
        const paymentMethodRadios = Array.from(document.querySelectorAll('.payment-method-radio'));
        const paymentCreditCardBox = document.querySelector('.payment-credit-card');
        const paymentBankTransferBox = document.querySelector('.payment-bank-transfer');

        function syncBillingFromShipping() {
            if (!useShippingCheckbox.checked) {
                return;
            }
            const selected = shippingRadios.find((input) => input.checked);
            if (!selected) {
                return;
            }
            hiddenBillingInput.value = selected.value;
            const label = document.querySelector('label[for="' + selected.id + '"]');
            if (selectedBillingText && label) selectedBillingText.textContent = label.textContent.trim();
        }

        function toggleBillingBox() {
            const sameAddress = useShippingCheckbox.checked;
            billingBox.classList.toggle('d-none', sameAddress);
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
            corporateFields.forEach((el) => el.classList.toggle('d-none', !isCorporate));
        }
        function togglePaymentMethod() {
            const selected = paymentMethodRadios.find((radio) => radio.checked);
            const method = selected ? selected.value : 'credit_card';
            if (paymentCreditCardBox) paymentCreditCardBox.classList.toggle('d-none', method !== 'credit_card');
            if (paymentBankTransferBox) paymentBankTransferBox.classList.toggle('d-none', method !== 'bank_transfer');
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
        }
        function closeModal() {
            if (!modalEl) return;
            modalEl.classList.add('hidden');
            modalEl.classList.remove('flex');
        }
        function syncCheckoutSubmit() {
            if (checkoutSubmit) checkoutSubmit.disabled = !(distanceCheckbox && distanceCheckbox.checked);
        }

        if (distanceBody) {
            distanceBody.addEventListener('scroll', function () {
                if (distanceBody.scrollTop + distanceBody.clientHeight >= distanceBody.scrollHeight - 8) {
                    scrolledToEnd = true;
                    if (confirmDistanceBtn) confirmDistanceBtn.disabled = false;
                    if (scrolledAtInput && !scrolledAtInput.value) scrolledAtInput.value = new Date().toISOString();
                }
            });
        }
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
    });
</script>
@endsection
