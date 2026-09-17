@extends('layouts.account')

@section('title', 'Adres Düzenle - BaskıYeri')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Adres Düzenle</h1>
        <p class="text-xs text-muted mt-0.5">Kayıtlı teslimat ve fatura adresi bilgilerini güncelleyin</p>
    </div>
    <div>
        <a href="{{ route('account.adresler.index') }}" class="btn btn-secondary text-xs">
            ← Adreslerime Dön
        </a>
    </div>
</div>

@if(session('success'))
    <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-7">
        <form action="{{ route('account.adresler.update', $address) }}" method="post" class="by-card p-6 bg-surface border border-border space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Adres Başlığı</label>
                <input type="text" name="label" class="form-control text-xs" value="{{ old('label', $address->label) }}">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Ad Soyad <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" class="form-control text-xs" value="{{ old('full_name', $address->full_name) }}" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Telefon <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" class="form-control text-xs" value="{{ old('phone', $address->phone) }}" required>
                </div>
            </div>

            @include('customer.addresses._geo_fields', ['address' => $address])

            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Adres Tarifi / Ek Bilgi</label>
                <input type="text" name="line1" class="form-control text-xs" value="{{ old('line1', $address->line1) }}" placeholder="Apartman adı, kat, zil vs.">
            </div>

            <div class="space-y-2 pt-2 border-t border-border">
                <label class="flex items-center gap-2 text-xs text-ink cursor-pointer">
                    <input class="h-4 w-4 rounded border-border text-cta focus:ring-cta" type="checkbox" name="is_default" id="isd" value="1" @checked(old('is_default', $address->is_default))>
                    <span>Varsayılan teslimat adresi olarak ayarla</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-ink cursor-pointer">
                    <input class="h-4 w-4 rounded border-border text-cta focus:ring-cta" type="checkbox" name="is_billing_default" id="isbd" value="1" @checked(old('is_billing_default', $address->is_billing_default))>
                    <span>Varsayılan fatura adresi olarak ayarla</span>
                </label>
            </div>

            <div class="rounded-xl border border-border p-4 bg-canvas/40 space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-ink cursor-pointer">
                    <input class="h-4 w-4 rounded border-border text-cta focus:ring-cta" type="checkbox" name="billing_same_as_address" id="billingSameAsAddress" value="1" @checked(old('billing_same_as_address', true))>
                    <span>Fatura adresi teslimat adresi ile aynı olsun</span>
                </label>

                <div id="billingFieldsBox" class="{{ old('billing_same_as_address', true) ? 'd-none' : '' }} space-y-3 pt-3 border-t border-border">
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Fatura Tipi</label>
                        <select class="form-control text-xs" name="invoice_type" id="invoiceType">
                            <option value="individual" @selected(old('invoice_type', $billingProfile->invoice_type ?? 'corporate') === 'individual')>Bireysel</option>
                            <option value="corporate" @selected(old('invoice_type', $billingProfile->invoice_type ?? 'corporate') === 'corporate')>Kurumsal</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Fatura Ad Soyad</label>
                            <input type="text" name="invoice_full_name" class="form-control text-xs" value="{{ old('invoice_full_name', $billingProfile->full_name ?? auth()->user()->name) }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Fatura Telefon</label>
                            <input type="text" name="invoice_phone" class="form-control text-xs" value="{{ old('invoice_phone', $billingProfile->phone ?? '') }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Fatura E-posta</label>
                        <input type="email" name="invoice_email" class="form-control text-xs" value="{{ old('invoice_email', $billingProfile->email ?? auth()->user()->email) }}">
                    </div>

                    <div class="individual-only {{ old('invoice_type', $billingProfile->invoice_type ?? 'corporate') === 'individual' ? '' : 'd-none' }}">
                        <label class="block text-xs font-semibold text-muted mb-1">TCKN (İsteğe bağlı)</label>
                        <input type="text" name="invoice_identity_number" class="form-control text-xs" maxlength="16" value="{{ old('invoice_identity_number', $billingProfile->identity_number ?? '') }}">
                    </div>

                    <div class="corporate-only space-y-3 {{ old('invoice_type', $billingProfile->invoice_type ?? 'corporate') === 'corporate' ? '' : 'd-none' }}">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Şirket Ünvanı</label>
                            <input type="text" name="invoice_company_name" class="form-control text-xs" value="{{ old('invoice_company_name', $billingProfile->company_name ?? '') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Şirket Adresi</label>
                            <input type="text" name="invoice_company_address" class="form-control text-xs" value="{{ old('invoice_company_address', $billingProfile->company_address ?? '') }}">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-1">Vergi No</label>
                                <input type="text" name="invoice_tax_number" class="form-control text-xs" maxlength="16" value="{{ old('invoice_tax_number', $billingProfile->tax_number ?? '') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-1">Vergi Dairesi</label>
                                <input type="text" name="invoice_tax_office" class="form-control text-xs" value="{{ old('invoice_tax_office', $billingProfile->tax_office ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-cta text-xs py-2 px-6">Adresi Güncelle</button>
            </div>
        </form>
    </div>

    <div class="lg:col-span-5">
        <div class="by-card p-5 bg-surface border border-border">
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-border">
                <h2 class="font-heading text-sm font-bold text-ink">Mevcut Kayıtlı Adresler</h2>
                <span class="text-xs text-muted">{{ $savedAddresses->count() }} adet</span>
            </div>
            <div class="space-y-2">
                @forelse($savedAddresses as $item)
                    <div class="p-3 rounded-lg border {{ $item->id === $address->id ? 'border-cta bg-cta/5' : 'border-border bg-canvas/40' }} text-xs">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-ink">{{ $item->label ?: 'Adres' }}</span>
                            @if($item->is_default)<x-badge variant="neutral">Varsayılan</x-badge>@endif
                        </div>
                        <div class="text-muted">{{ $item->full_name }}</div>
                        <div class="text-muted mt-0.5">{{ $item->formatted }}</div>
                    </div>
                @empty
                    <p class="text-xs text-muted text-center py-4">Henüz kayıtlı adres bulunmuyor.</p>
                @endforelse
            </div>
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
