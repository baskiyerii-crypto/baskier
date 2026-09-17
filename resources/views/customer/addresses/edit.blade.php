@extends('layouts.account')

@section('title', 'Adres düzenle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Adres düzenle</h1>
    <a href="{{ route('account.adresler.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Adreslerim</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <form action="{{ route('account.adresler.update', $address) }}" method="post" class="bg-white rounded-4 shadow-sm p-4">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Adres adı</label>
                <input type="text" name="label" class="form-control" value="{{ old('label', $address->label) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Ad Soyad</label>
                <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $address->full_name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Telefon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $address->phone) }}" required>
            </div>
            @include('customer.addresses._geo_fields', ['address' => $address])
            <div class="mb-3">
                <label class="form-label">Adres tarifi (isteğe bağlı)</label>
                <input type="text" name="line1" class="form-control" value="{{ old('line1', $address->line1) }}" placeholder="Örn. apartman adı, kat, tarif">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_default" id="isd" value="1" @checked(old('is_default', $address->is_default))>
                <label class="form-check-label" for="isd">Varsayılan adres</label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_billing_default" id="isbd" value="1" @checked(old('is_billing_default', $address->is_billing_default))>
                <label class="form-check-label" for="isbd">Varsayılan fatura adresi</label>
            </div>
            <div class="border rounded-3 p-3 mb-3">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="billing_same_as_address" id="billingSameAsAddress" value="1" @checked(old('billing_same_as_address', true))>
                    <label class="form-check-label fw-semibold" for="billingSameAsAddress">Fatura adresi aynı olsun</label>
                </div>
                <div id="billingFieldsBox" class="{{ old('billing_same_as_address', true) ? 'd-none' : '' }}">
                    <div class="mb-2">
                        <label class="form-label">Fatura tipi</label>
                        <select class="form-select" name="invoice_type" id="invoiceType">
                            <option value="individual" @selected(old('invoice_type', $billingProfile->invoice_type ?? 'corporate') === 'individual')>Bireysel</option>
                            <option value="corporate" @selected(old('invoice_type', $billingProfile->invoice_type ?? 'corporate') === 'corporate')>Kurumsal</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Fatura Ad Soyad</label>
                            <input type="text" name="invoice_full_name" class="form-control" value="{{ old('invoice_full_name', $billingProfile->full_name ?? auth()->user()->name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fatura Telefon</label>
                            <input type="text" name="invoice_phone" class="form-control" value="{{ old('invoice_phone', $billingProfile->phone ?? '') }}">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fatura E-posta</label>
                        <input type="email" name="invoice_email" class="form-control" value="{{ old('invoice_email', $billingProfile->email ?? auth()->user()->email) }}">
                    </div>
                    <div class="individual-only mb-2 {{ old('invoice_type', $billingProfile->invoice_type ?? 'corporate') === 'individual' ? '' : 'd-none' }}">
                        <label class="form-label">TCKN (isteğe bağlı)</label>
                        <input type="text" name="invoice_identity_number" class="form-control" maxlength="16" value="{{ old('invoice_identity_number', $billingProfile->identity_number ?? '') }}">
                    </div>
                    <div class="corporate-only {{ old('invoice_type', $billingProfile->invoice_type ?? 'corporate') === 'corporate' ? '' : 'd-none' }}">
                        <div class="mb-2">
                            <label class="form-label">Şirket ismi</label>
                            <input type="text" name="invoice_company_name" class="form-control" value="{{ old('invoice_company_name', $billingProfile->company_name ?? '') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Şirket adresi</label>
                            <input type="text" name="invoice_company_address" class="form-control" value="{{ old('invoice_company_address', $billingProfile->company_address ?? '') }}">
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Vergi No</label>
                                <input type="text" name="invoice_tax_number" class="form-control" maxlength="16" value="{{ old('invoice_tax_number', $billingProfile->tax_number ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vergi Dairesi</label>
                                <input type="text" name="invoice_tax_office" class="form-control" value="{{ old('invoice_tax_office', $billingProfile->tax_office ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-warning rounded-pill">Güncelle</button>
        </form>
    </div>
    <div class="col-lg-5">
        <div class="bg-white rounded-4 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h6 mb-0">Kayıtlı adresler</h2>
                <span class="small text-muted">{{ $savedAddresses->count() }} adet</span>
            </div>
            @forelse($savedAddresses as $item)
                <div class="border rounded-3 p-2 mb-2 {{ $item->id === $address->id ? 'border-warning' : '' }}">
                    <div class="fw-semibold">{{ $item->label ?: 'Adres' }} @if($item->is_default)<span class="badge bg-secondary">Varsayılan</span>@endif</div>
                    <div class="small text-muted">{{ $item->full_name }}</div>
                    <div class="small text-muted">{{ $item->formatted }}</div>
                </div>
            @empty
                <p class="small text-muted mb-0">Henüz kayıtlı adres yok.</p>
            @endforelse
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sameBox = document.getElementById('billingSameAsAddress');
        const fieldsBox = document.getElementById('billingFieldsBox');
        const invoiceType = document.getElementById('invoiceType');
        const corporateFields = document.querySelectorAll('.corporate-only');
        const individualFields = document.querySelectorAll('.individual-only');
        if (!sameBox || !fieldsBox || !invoiceType) return;
        function toggleBillingFields() {
            fieldsBox.classList.toggle('d-none', sameBox.checked);
        }
        function toggleInvoiceTypeFields() {
            const isCorporate = invoiceType.value === 'corporate';
            corporateFields.forEach((el) => el.classList.toggle('d-none', !isCorporate));
            individualFields.forEach((el) => el.classList.toggle('d-none', isCorporate));
        }
        sameBox.addEventListener('change', toggleBillingFields);
        invoiceType.addEventListener('change', toggleInvoiceTypeFields);
        toggleBillingFields();
        toggleInvoiceTypeFields();
    });
</script>
@endsection
