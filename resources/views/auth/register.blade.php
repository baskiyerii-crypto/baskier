@extends('layouts.app')

@section('title', 'Kayıt Ol - BaskıYeri')

@section('content')
<div class="by-container py-12 md:py-16">
    <div class="mx-auto max-w-xl">
        <div class="mb-6 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-muted">Aramıza Katılın</p>
            <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink mt-1">Hesap Oluşturun</h1>
            <p class="mt-1.5 text-xs text-muted">Müşteri olarak alışveriş yapın veya üretici/uzman olarak mağazanızı açın.</p>
        </div>

        @if (session('info'))
            <x-alert type="info" class="mb-6">{{ session('info') }}</x-alert>
        @endif

        <div class="by-card p-6 md:p-8 bg-surface border border-border">
            <form method="POST" action="{{ route('register') }}" class="space-y-4" enctype="multipart/form-data">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-semibold text-muted mb-1">Ad Soyad <span class="text-red-500">*</span></label>
                        <input type="text" class="form-control text-xs" id="name" name="name" value="{{ old('name') }}" required placeholder="Adınız ve Soyadınız">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="email" class="block text-xs font-semibold text-muted mb-1">E-posta Adresi <span class="text-red-500">*</span></label>
                        <input type="email" class="form-control text-xs" id="email" name="email" value="{{ old('email') }}" required placeholder="ornek@baskiyeri.com">
                    </div>
                    <div>
                        <label for="password" class="block text-xs font-semibold text-muted mb-1">Şifre <span class="text-red-500">*</span></label>
                        <input type="password" class="form-control text-xs" id="password" name="password" required placeholder="En az 8 karakter">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-muted mb-1">Şifre Tekrar <span class="text-red-500">*</span></label>
                        <input type="password" class="form-control text-xs" id="password_confirmation" name="password_confirmation" required placeholder="Şifrenizi doğrulayın">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-muted mb-2">Hesap Türü</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-border bg-canvas/50 cursor-pointer hover:border-cta/50 transition-colors text-xs font-semibold text-ink">
                            <input type="radio" class="h-4 w-4 text-cta focus:ring-cta" id="role_customer" name="role" value="customer"
                                   {{ old('role', 'customer') === 'customer' ? 'checked' : '' }}
                                   onchange="window.toggleVendorBiz && window.toggleVendorBiz()">
                            <span>Müşteri</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-border bg-canvas/50 cursor-pointer hover:border-cta/50 transition-colors text-xs font-semibold text-ink">
                            <input type="radio" class="h-4 w-4 text-cta focus:ring-cta" id="role_vendor" name="role" value="vendor"
                                   {{ old('role') === 'vendor' ? 'checked' : '' }}
                                   onchange="window.toggleVendorBiz && window.toggleVendorBiz()">
                            <span>Satıcı / Üretici</span>
                        </label>
                    </div>
                </div>

                <div id="vendor-extra-fields" class="p-5 rounded-xl border border-border bg-canvas/40 space-y-4" style="display:none;">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-ink">{{ __('panel.vendor_tracks') }}</p>
                        <p class="text-[11px] text-muted mt-0.5">{{ __('panel.vendor_tracks_help') }}</p>
                        <div class="mt-3 space-y-2">
                            @foreach([
                                'physical_products' => __('panel.track_physical_products'),
                                'physical_quote' => __('panel.track_physical_quote'),
                                'freelancer' => __('panel.track_freelancer'),
                            ] as $track => $label)
                                <label class="flex items-center gap-2.5 p-3 rounded-lg border border-border bg-surface text-xs text-ink cursor-pointer">
                                    <input class="h-4 w-4 track-cb rounded text-cta focus:ring-cta" type="checkbox" name="registration_tracks[]" value="{{ $track }}"
                                           @checked(in_array($track, old('registration_tracks', [])))
                                           onchange="window.toggleVendorTracks && window.toggleVendorTracks()">
                                    <span class="font-medium">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('registration_tracks')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                    </div>

                    @if(isset($businessTypes) && $businessTypes->isNotEmpty())
                        <div class="pt-3 border-t border-border">
                            <p class="text-xs font-bold uppercase tracking-wider text-ink">{{ __('panel.business_types') }}</p>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                @foreach($businessTypes as $bt)
                                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-border bg-surface text-xs text-ink cursor-pointer">
                                        <input class="h-4 w-4 rounded text-cta focus:ring-cta" type="checkbox" name="business_type_ids[]" value="{{ $bt->id }}"
                                               @checked(in_array($bt->id, old('business_type_ids', [])))>
                                        <span>{{ $bt->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div id="physical-tax-fields" class="pt-3 border-t border-border space-y-3" style="display:none;">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-ink">{{ __('panel.tax_info') }}</p>
                            <p class="text-[11px] text-muted mt-0.5">{{ __('panel.tax_plate_required_help') }}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.company_name') }}</label>
                                <input type="text" class="form-control text-xs" name="company_name" value="{{ old('company_name') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.tax_office') }}</label>
                                <input type="text" class="form-control text-xs" name="tax_office" value="{{ old('tax_office') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.tax_number') }}</label>
                                <input type="text" class="form-control text-xs" name="tax_number" value="{{ old('tax_number') }}">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.tax_plate') }}</label>
                                <input type="file" class="form-control text-xs" name="tax_plate" accept=".pdf,image/*">
                                @error('tax_plate')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div id="freelancer-doc-fields" class="pt-3 border-t border-border space-y-3" style="display:none;">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-ink">{{ __('panel.freelancer_docs') }}</p>
                            <p class="text-[11px] text-muted mt-0.5">{{ __('panel.freelancer_docs_help') }}</p>
                        </div>
                        <div class="space-y-2" id="freelancer-doc-list">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <select name="freelancer_doc_types[]" class="form-control text-xs">
                                    <option value="diploma">{{ __('panel.doc_diploma') }}</option>
                                    <option value="certificate">{{ __('panel.doc_certificate') }}</option>
                                    <option value="course">{{ __('panel.doc_course') }}</option>
                                    <option value="other">{{ __('panel.doc_other') }}</option>
                                </select>
                                <input type="file" name="freelancer_docs[]" class="form-control text-xs" accept=".pdf,image/*">
                            </div>
                        </div>
                        @error('freelancer_docs')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div class="pt-3 border-t border-border" id="vendor-agreement-gate">
                        @include('partials.legal-scroll-gate', [
                            'slug' => 'vendor_agreement',
                            'label' => __('panel.vendor_agreement'),
                            'field' => 'accept_vendor_agreement',
                            'contractHtml' => $vendorAgreement->content_html ?? null,
                        ])
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-border bg-canvas/30 space-y-3">
                    @include('partials.legal-scroll-gate', [
                        'slug' => 'terms',
                        'label' => __('ui.terms'),
                        'field' => 'accept_terms',
                        'contractHtml' => $termsContract->content_html ?? null,
                    ])
                    @include('partials.legal-scroll-gate', [
                        'slug' => 'privacy',
                        'label' => __('ui.privacy'),
                        'field' => 'accept_privacy',
                        'contractHtml' => $privacyContract->content_html ?? null,
                    ])
                </div>

                <button type="submit" class="btn btn-cta w-full text-xs py-2.5 font-bold">Kayıt Ol ve Başla</button>
            </form>

            <div class="mt-6 pt-6 border-t border-border text-center text-xs text-muted">
                Zaten hesabınız var mı? <a href="{{ route('login') }}" class="font-bold text-cta hover:underline">Giriş Yapın</a>
            </div>
        </div>
    </div>
</div>

<script>
window.toggleVendorBiz = function () {
    var v = document.getElementById('role_vendor');
    var box = document.getElementById('vendor-extra-fields');
    if (!box) return;
    box.style.display = v && v.checked ? 'block' : 'none';
    window.toggleVendorTracks && window.toggleVendorTracks();
};
window.toggleVendorTracks = function () {
    var checks = document.querySelectorAll('.track-cb:checked');
    var tracks = Array.from(checks).map(function (c) { return c.value; });
    var physical = tracks.indexOf('physical_products') >= 0 || tracks.indexOf('physical_quote') >= 0;
    var freelancer = tracks.indexOf('freelancer') >= 0;
    var tax = document.getElementById('physical-tax-fields');
    var docs = document.getElementById('freelancer-doc-fields');
    if (tax) tax.style.display = physical ? 'block' : 'none';
    if (docs) docs.style.display = freelancer ? 'block' : 'none';
};
document.addEventListener('DOMContentLoaded', function () {
    try {
        var params = new URLSearchParams(window.location.search);
        if (params.get('role') === 'vendor') {
            var r = document.getElementById('role_vendor');
            if (r) r.checked = true;
        }
    } catch (e) {}
    window.toggleVendorBiz();
});
</script>
@endsection
