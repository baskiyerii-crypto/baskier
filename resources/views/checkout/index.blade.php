@extends('layouts.app')

@section('title', __('ui.checkout_title') . ' - BaskıYeri')

@section('content')
<div class="content-shell py-4">
    <h1 class="h5 mb-4">{{ __('ui.checkout_title') }}</h1>
    <p class="small text-muted mb-4">Teslimat ve fatura bilgilerinizi kontrol edin, ardından ödeme yönteminizi seçin.</p>
    @if(!app(\App\Services\PaymentService::class)->isConfigured() || app(\App\Services\PaymentService::class)->mode() !== 'live')
        <div class="alert alert-warning mb-4" role="status">Kart ödemesi test modunda. Gerçek kart bilgilerinizi kullanmayın; bu modda gerçek tahsilat yapılmaz.</div>
    @endif

    @if(! empty($quickBuy))
        <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
            <div>
                <strong>Hızlı Satın Alma:</strong> Şu anda seçtiğiniz ürün için özel satın alma oturumundasınız. Sepetinizdeki diğer ürünler korunur ve etkilenmez.
            </div>
            <a href="{{ route('checkout.cancel-quick-buy') }}" class="btn btn-sm btn-outline-secondary ms-3">Normal Sepete Dön</a>
        </div>
    @endif

    @if($items->isEmpty())
        <p><a href="{{ route('cart.index') }}">Sepete dön</a></p>
    @elseif($addresses->isEmpty())
        <div class="bg-white rounded-4 shadow-sm p-4" style="max-width:640px;">
            <h2 class="h6 mb-2">Teslimat adresi ekleyin</h2>
            <p class="small text-muted mb-3">Ödemeye devam etmek için önce bir adres kaydedin. Kaydettikten sonra bu ödeme ekranına dönersiniz.</p>
            <form action="{{ route('account.adresler.store') }}" method="post">
                @csrf
                <input type="hidden" name="redirect" value="checkout">
                <div class="mb-3">
                    <label class="form-label">Adres adı</label>
                    <input type="text" name="label" class="form-control" value="{{ old('label', 'Ev') }}" placeholder="Ev, İş, Ofis...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ad Soyad</label>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefon</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                </div>
                @include('customer.addresses._geo_fields', ['address' => null])
                <div class="mb-3">
                    <label class="form-label">Adres tarifi (isteğe bağlı)</label>
                    <input type="text" name="line1" class="form-control" value="{{ old('line1') }}">
                </div>
                <input type="hidden" name="is_default" value="1">
                <input type="hidden" name="is_billing_default" value="1">
                <button class="btn btn-warning rounded-pill">Adresi kaydet ve ödemeye dön</button>
            </form>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-7">
                <form action="{{ route('checkout.store') }}" method="post" id="checkout-form" class="bg-white rounded-4 shadow-sm p-4">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
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
                    <a href="{{ route('account.adresler.create', ['redirect' => 'checkout']) }}" class="small">+ Yeni adres</a>
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
                        <div class="form-check mb-3">
                            <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="payCreditCard" value="credit_card" @checked(old('payment_method', 'credit_card') === 'credit_card')>
                            <label class="form-check-label fw-bold" for="payCreditCard">
                                Kredi / Banka Kartı (iyzico Güvenli Ödeme)
                            </label>
                            <div class="small text-muted mt-1">
                                Siparişinizi onayladıktan sonra 256-bit SSL korumalı iyzico 3DS ödeme ekranına yönlendirilirsiniz. Kart bilgileriniz sunucularımızda saklanmaz.
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="payBankTransfer" value="bank_transfer" @checked(old('payment_method') === 'bank_transfer')>
                            <label class="form-check-label fw-bold" for="payBankTransfer">
                                Banka Havalesi / EFT
                            </label>
                            <div class="payment-method-box payment-bank-transfer mt-2 {{ old('payment_method') === 'bank_transfer' ? '' : 'd-none' }}">
                                @php $ibanTr = app()->getLocale() === 'tr'; $oldIban = strtoupper(preg_replace('/\s+/', '', (string) old('bank_iban', ''))); @endphp
                                <label class="form-label small">Gönderim yapacağınız IBAN</label>
                                @if($ibanTr)
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text font-monospace">TR</span>
                                        <input type="text" id="bank-iban-digits" class="form-control font-monospace" inputmode="numeric" pattern="[0-9]*" maxlength="24" autocomplete="off" value="{{ str_starts_with($oldIban, 'TR') ? substr($oldIban, 2) : $oldIban }}" placeholder="24 hane rakam">
                                    </div>
                                    <input type="hidden" name="bank_iban" id="bank-iban-full" value="{{ $oldIban }}">
                                @else
                                    <input type="text" name="bank_iban" class="form-control form-control-sm mb-1 font-monospace text-uppercase" maxlength="34" value="{{ $oldIban }}" placeholder="IBAN (max 34)">
                                @endif
                                <p class="small text-muted mb-0">Havale/EFT ile ödemelerde siparişiniz kaydedilir ve yönetici onayı sonrasında üretime alınır.</p>
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="payCashOnDelivery" value="cash_on_delivery" @checked(old('payment_method') === 'cash_on_delivery')>
                            <label class="form-check-label fw-bold" for="payCashOnDelivery">
                                Kapıda Ödeme
                            </label>
                            <div class="small text-muted mt-1">
                                Siparişiniz onay bekliyor durumunda oluşturulur.
                            </div>
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
                            </label>
                        </div>
                        <input type="hidden" name="contract_scrolled_at" id="contract_scrolled_at" value="">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="accept-kvkk" name="accept_kvkk" value="1" disabled>
                            <label class="form-check-label" for="accept-kvkk">
                                <strong>KVKK Aydınlatma Metni</strong>'ni okudum.
                            </label>
                        </div>
                        <input type="hidden" name="kvkk_scrolled_at" id="kvkk_scrolled_at" value="">
                        <div class="form-text">Kutuya tıklayınca metin açılır. Sonuna kadar kaydırmadan onaylanamaz.</div>
                    </div>
                    <button type="submit" class="btn btn-warning rounded-pill px-5" id="checkout-submit" disabled>Siparişi tamamla</button>
                </form>

                <div id="legalModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
                    <div class="flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b px-4 py-3">
                            <h5 class="m-0 text-base font-semibold" id="legal-modal-title">Sözleşme</h5>
                            <button type="button" id="close-legal-modal" class="btn btn-sm btn-outline-secondary">Kapat</button>
                        </div>
                        <div class="flex-1 overflow-auto p-4" id="legal-modal-body" style="max-height:55vh;">
                            <div id="legal-mss-content" class="d-none">{!! $distanceSalesContract?->content_html ?? '<p>Sözleşme metni henüz tanımlanmadı.</p>' !!}</div>
                            <div id="legal-kvkk-content" class="d-none">{!! $kvkkContract?->content_html ?? '<p>KVKK metni henüz tanımlanmadı.</p>' !!}</div>
                        </div>
                        <div class="flex justify-end gap-2 border-t px-4 py-3">
                            <button type="button" class="btn btn-primary" id="confirm-legal" disabled>Okudum, onayla</button>
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
        if (!useShippingCheckbox) return;
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
        const kvkkCheckbox = document.getElementById('accept-kvkk');
        const checkoutSubmit = document.getElementById('checkout-submit');
        const scrolledAtInput = document.getElementById('contract_scrolled_at');
        const kvkkScrolledAtInput = document.getElementById('kvkk_scrolled_at');
        const modalEl = document.getElementById('legalModal');
        const modalTitle = document.getElementById('legal-modal-title');
        const modalBody = document.getElementById('legal-modal-body');
        const mssContent = document.getElementById('legal-mss-content');
        const kvkkContent = document.getElementById('legal-kvkk-content');
        const confirmLegalBtn = document.getElementById('confirm-legal');
        const closeLegalBtn = document.getElementById('close-legal-modal');
        const ibanDigits = document.getElementById('bank-iban-digits');
        const ibanFull = document.getElementById('bank-iban-full');
        let legalKind = null;
        const scrolled = { mss: false, kvkk: false };

        function syncIban() {
            if (!ibanDigits || !ibanFull) return;
            ibanDigits.value = ibanDigits.value.replace(/\D/g, '').slice(0, 24);
            ibanFull.value = 'TR' + ibanDigits.value;
        }
        ibanDigits?.addEventListener('input', syncIban);
        ibanDigits?.addEventListener('keydown', function (e) {
            if (e.ctrlKey || e.metaKey || e.altKey) return;
            if (e.key.length === 1 && !/[0-9]/.test(e.key)) e.preventDefault();
        });
        syncIban();

        function openLegal(kind) {
            legalKind = kind;
            if (mssContent) mssContent.classList.toggle('d-none', kind !== 'mss');
            if (kvkkContent) kvkkContent.classList.toggle('d-none', kind !== 'kvkk');
            if (modalTitle) modalTitle.textContent = kind === 'kvkk' ? 'KVKK Aydınlatma Metni' : 'Mesafeli Satış Sözleşmesi';
            if (confirmLegalBtn) confirmLegalBtn.disabled = !scrolled[kind];
            if (modalEl) {
                modalEl.classList.remove('hidden');
                modalEl.classList.add('flex');
            }
            if (modalBody) modalBody.scrollTop = 0;
            checkLegalScroll();
        }
        function closeLegal() {
            if (!modalEl) return;
            modalEl.classList.add('hidden');
            modalEl.classList.remove('flex');
        }
        function syncCheckoutSubmit() {
            if (checkoutSubmit) {
                checkoutSubmit.disabled = !(distanceCheckbox && distanceCheckbox.checked && kvkkCheckbox && kvkkCheckbox.checked);
            }
        }
        function checkLegalScroll() {
            if (!modalBody || !legalKind) return;
            if (modalBody.scrollTop + modalBody.clientHeight >= modalBody.scrollHeight - 8) {
                scrolled[legalKind] = true;
                if (confirmLegalBtn) confirmLegalBtn.disabled = false;
                const stamp = new Date().toISOString();
                if (legalKind === 'mss' && scrolledAtInput && !scrolledAtInput.value) scrolledAtInput.value = stamp;
                if (legalKind === 'kvkk' && kvkkScrolledAtInput && !kvkkScrolledAtInput.value) kvkkScrolledAtInput.value = stamp;
            }
        }
        modalBody?.addEventListener('scroll', checkLegalScroll);
        closeLegalBtn?.addEventListener('click', closeLegal);
        [distanceCheckbox, kvkkCheckbox].forEach(function (box) {
            box?.addEventListener('click', function (e) {
                const kind = box.id === 'accept-kvkk' ? 'kvkk' : 'mss';
                if (!box.checked && !scrolled[kind]) {
                    e.preventDefault();
                    openLegal(kind);
                }
                syncCheckoutSubmit();
            });
        });
        confirmLegalBtn?.addEventListener('click', function () {
            if (!legalKind || !scrolled[legalKind]) return;
            const box = legalKind === 'kvkk' ? kvkkCheckbox : distanceCheckbox;
            if (box) {
                box.disabled = false;
                box.checked = true;
            }
            syncCheckoutSubmit();
            closeLegal();
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
