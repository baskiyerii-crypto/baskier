@extends('layouts.app')

@section('title', 'Kayıt Ol - BaskıYeri')

@section('content')
<div class="by-container py-10">
    <div class="mx-auto max-w-xl">
        <div class="mb-6 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Başlayalım</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Kayıt ol</h1>
            <p class="mt-2 text-sm text-slate-600">Müşteri ya da satıcı hesabı oluştur.</p>
        </div>

        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        <div class="by-card p-6 md:p-8">

            <form method="POST" action="{{ route('register') }}" class="mt-4 space-y-4" enctype="multipart/form-data">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="name" class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control mt-1" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="md:col-span-2">
                        <label for="email" class="form-label">E-posta</label>
                        <input type="email" class="form-control mt-1" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div>
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control mt-1" id="password" name="password" required>
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Şifre (tekrar)</label>
                        <input type="password" class="form-control mt-1" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>

                <div>
                    <label class="form-label">Hesap türü</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm font-semibold text-slate-800">
                            <input type="radio" class="h-4 w-4" id="role_customer" name="role" value="customer"
                                   {{ old('role', 'customer') === 'customer' ? 'checked' : '' }}
                                   onchange="window.toggleVendorBiz && window.toggleVendorBiz()">
                            Müşteri
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm font-semibold text-slate-800">
                            <input type="radio" class="h-4 w-4" id="role_vendor" name="role" value="vendor"
                                   {{ old('role') === 'vendor' ? 'checked' : '' }}
                                   onchange="window.toggleVendorBiz && window.toggleVendorBiz()">
                            Satıcı
                        </label>
                    </div>
                </div>

                <div id="vendor-extra-fields" class="by-card border-slate-200 bg-white/60 p-5" style="display:none;">
                    @if(isset($businessTypes) && $businessTypes->isNotEmpty())
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Satıcı iş kolu</p>
                        <p class="mt-1 text-sm text-slate-600">En az birini seçin.</p>
                        <div class="mt-4 grid gap-2 md:grid-cols-2">
                            @foreach($businessTypes as $bt)
                                <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm text-slate-800">
                                    <input class="h-4 w-4" type="checkbox" name="business_type_ids[]" id="bt{{ $bt->id }}" value="{{ $bt->id }}"
                                           @checked(in_array($bt->id, old('business_type_ids', [])))>
                                    <span class="font-semibold">{{ $bt->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('business_type_ids')<div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>@enderror

                        <div class="mt-6 by-divider"></div>
                    @endif

                    <p class="mt-{{ (isset($businessTypes) && $businessTypes->isNotEmpty()) ? '6' : '0' }} text-xs font-bold uppercase tracking-wider text-slate-500">Vergi bilgileri</p>
                    <p class="mt-1 text-sm text-slate-600">Başvurunuz admin onayına düşer. Vergi levhası yüklemek zorunludur.</p>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="form-label">Firma / Ünvan</label>
                            <input type="text" class="form-control mt-1" name="company_name" value="{{ old('company_name') }}" placeholder="Örn: ABC Matbaa LTD ŞTİ">
                        </div>
                        <div>
                            <label class="form-label">Vergi dairesi</label>
                            <input type="text" class="form-control mt-1" name="tax_office" value="{{ old('tax_office') }}" placeholder="Örn: Kadıköy">
                        </div>
                        <div>
                            <label class="form-label">Vergi no / TCKN</label>
                            <input type="text" class="form-control mt-1" name="tax_number" value="{{ old('tax_number') }}" placeholder="Örn: 1234567890">
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Vergi levhası (PDF/JPG/PNG)</label>
                            <input type="file" class="form-control mt-1" name="tax_plate" accept=".pdf,image/*">
                            @error('tax_plate')<div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-6 by-divider"></div>
                    <label class="mt-4 flex items-start gap-3 text-sm text-slate-700">
                        <input class="mt-1 h-4 w-4 rounded border-slate-300" type="checkbox" name="accept_vendor_agreement" value="1" @checked(old('accept_vendor_agreement'))>
                        <span>
                            <span class="font-semibold">Satıcı Sözleşmesi</span>'ni okudum ve kabul ediyorum.
                            <a class="by-link" target="_blank" href="{{ route('contracts.show', 'vendor_agreement') }}">Görüntüle</a>
                        </span>
                    </label>
                </div>

                <div class="by-surface-indigo p-4">
                    <div class="by-accent-bar mb-3"></div>
                    <div class="space-y-2 text-sm text-slate-700">
                        <label class="flex items-start gap-3">
                            <input class="mt-1 h-4 w-4 rounded border-slate-300" type="checkbox" name="accept_terms" value="1" @checked(old('accept_terms')) required>
                            <span>
                                <span class="font-semibold">Kullanım Koşulları</span>'nı okudum ve kabul ediyorum.
                                <a class="by-link" target="_blank" href="{{ route('contracts.show', 'terms') }}">Görüntüle</a>
                            </span>
                        </label>
                        <label class="flex items-start gap-3">
                            <input class="mt-1 h-4 w-4 rounded border-slate-300" type="checkbox" name="accept_privacy" value="1" @checked(old('accept_privacy')) required>
                            <span>
                                <span class="font-semibold">Gizlilik Politikası</span>'nı okudum ve kabul ediyorum.
                                <a class="by-link" target="_blank" href="{{ route('contracts.show', 'privacy') }}">Görüntüle</a>
                            </span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full by-btn-cta">Kayıt Ol</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Zaten hesabınız var mı? <a href="{{ route('login') }}" class="by-link">Giriş yapın</a>
            </p>
        </div>
    </div>
</div>
<script>
window.toggleVendorBiz = function () {
    var v = document.getElementById('role_vendor');
    var box = document.getElementById('vendor-extra-fields');
    if (!box) return;
    box.style.display = v && v.checked ? 'block' : 'none';
};
document.addEventListener('DOMContentLoaded', function () {
    try {
        var params = new URLSearchParams(window.location.search);
        var role = params.get('role');
        if (role === 'vendor') {
            var r = document.getElementById('role_vendor');
            if (r) r.checked = true;
        }
    } catch (e) {}
    window.toggleVendorBiz();
});
</script>
@endsection
