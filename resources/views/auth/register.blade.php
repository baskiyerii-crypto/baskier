@extends('layouts.app')

@section('title', 'Kayıt Ol - BaskıYeri')

@section('content')
<div class="by-container py-10">
    <div class="mx-auto max-w-xl">
        <div class="mb-6 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Başlayalım</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Kayıt ol</h1>
            <p class="mt-2 text-sm text-slate-600">Müşteri, satıcı veya açık hava hesabı oluştur.</p>
        </div>

        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        <div class="by-card p-6 md:p-8">

            <form method="POST" action="{{ route('register') }}" class="mt-4 space-y-4" enctype="multipart/form-data" id="register-form">
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
                            <input type="radio" class="h-4 w-4 role-radio" id="role_customer" name="role" value="customer"
                                   {{ old('role', 'customer') === 'customer' ? 'checked' : '' }}
                                   onchange="window.toggleRegisterKind && window.toggleRegisterKind()">
                            Müşteri
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm font-semibold text-slate-800">
                            <input type="radio" class="h-4 w-4 role-radio" id="role_vendor" name="role" value="vendor"
                                   {{ old('role') === 'vendor' ? 'checked' : '' }}
                                   onchange="window.toggleRegisterKind && window.toggleRegisterKind()">
                            Satıcı
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/70 px-4 py-3 text-sm font-semibold text-emerald-900">
                            <input type="radio" class="h-4 w-4 role-radio" id="role_outdoor" name="role" value="outdoor"
                                   {{ old('role') === 'outdoor' ? 'checked' : '' }}
                                   onchange="window.toggleRegisterKind && window.toggleRegisterKind()">
                            Açık hava
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Açık hava (pano / OOH) satıcı kollarından ayrı bir hesap türüdür.</p>
                </div>

                {{-- Satıcı (matbaa / teklif / freelancer) --}}
                <div id="vendor-extra-fields" class="by-card border-slate-200 bg-white/60 p-5" style="display:none;" data-register-panel="vendor">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('panel.vendor_tracks') }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ __('panel.vendor_tracks_help') }}</p>
                    <div class="mt-4 grid gap-2 md:grid-cols-1">
                        @foreach([
                            'physical_products' => __('panel.track_physical_products'),
                            'physical_quote' => __('panel.track_physical_quote'),
                            'freelancer' => __('panel.track_freelancer'),
                        ] as $track => $label)
                            <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm text-slate-800">
                                <input class="h-4 w-4 track-cb" type="checkbox" name="registration_tracks[]" value="{{ $track }}"
                                       @checked(in_array($track, old('registration_tracks', [])))
                                       onchange="window.toggleVendorTracks && window.toggleVendorTracks()">
                                <span class="font-semibold">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('registration_tracks')<div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>@enderror

                    @if(isset($businessTypes) && $businessTypes->isNotEmpty())
                        <div class="mt-6 by-divider"></div>
                        <p class="mt-6 text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('panel.business_types') }}</p>
                        <div class="mt-4 grid gap-2 md:grid-cols-2">
                            @foreach($businessTypes as $bt)
                                <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm text-slate-800">
                                    <input class="h-4 w-4" type="checkbox" name="business_type_ids[]" value="{{ $bt->id }}"
                                           @checked(in_array($bt->id, old('business_type_ids', [])))>
                                    <span class="font-semibold">{{ $bt->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6 by-divider"></div>
                    <p class="mt-6 text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('panel.location') }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ __('panel.location_help') }}</p>
                    <div class="mt-4">
                        @include('partials.geo-location-fields', [
                            'countryValue' => old('country_code', 'TR'),
                            'cityValue' => old('city', ''),
                            'districtValue' => old('district', ''),
                            'countries' => $countries ?? collect(),
                            'provinces' => $provinces ?? collect(),
                            'trDistricts' => $districts ?? collect(),
                            'citySuggestions' => $citySuggestions ?? [],
                            'districtSuggestions' => $districtSuggestions ?? [],
                            'idPrefix' => 'reg-vendor-geo',
                        ])
                    </div>

                    <div id="physical-tax-fields" class="mt-6" style="display:none;">
                        <div class="by-divider mb-4"></div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('panel.tax_info') }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ __('panel.tax_plate_required_help') }}</p>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="form-label">{{ __('panel.company_name') }}</label>
                                <input type="text" class="form-control mt-1" name="company_name" value="{{ old('company_name') }}">
                            </div>
                            <div>
                                <label class="form-label">{{ __('panel.tax_office') }}</label>
                                <input type="text" class="form-control mt-1" name="tax_office" value="{{ old('tax_office') }}">
                            </div>
                            <div>
                                <label class="form-label">{{ __('panel.tax_number') }}</label>
                                <input type="text" class="form-control mt-1" name="tax_number" value="{{ old('tax_number') }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="form-label">{{ __('panel.tax_plate') }}</label>
                                <input type="file" class="form-control mt-1" name="tax_plate" accept=".pdf,image/*">
                                @error('tax_plate')<div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div id="freelancer-doc-fields" class="mt-6" style="display:none;">
                        <div class="by-divider mb-4"></div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('panel.freelancer_docs') }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ __('panel.freelancer_docs_help') }}</p>
                        <div class="mt-3 space-y-3" id="freelancer-doc-list">
                            <div class="grid gap-2 md:grid-cols-2">
                                <select name="freelancer_doc_types[]" class="form-select">
                                    <option value="diploma">{{ __('panel.doc_diploma') }}</option>
                                    <option value="certificate">{{ __('panel.doc_certificate') }}</option>
                                    <option value="course">{{ __('panel.doc_course') }}</option>
                                    <option value="other">{{ __('panel.doc_other') }}</option>
                                </select>
                                <input type="file" name="freelancer_docs[]" class="form-control" accept=".pdf,image/*">
                            </div>
                        </div>
                        @error('freelancer_docs')<div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>@enderror
                    </div>

                    <div class="mt-6 by-divider"></div>
                    <div class="mt-4">
                        @include('partials.legal-scroll-gate', [
                            'slug' => 'vendor_agreement',
                            'label' => __('panel.vendor_agreement'),
                            'field' => 'accept_vendor_agreement',
                            'contractHtml' => $vendorAgreement->content_html ?? null,
                        ])
                    </div>
                </div>

                {{-- Açık hava (ayrı kayıt türü) --}}
                <div id="outdoor-extra-fields" class="by-card border-emerald-200 bg-emerald-50/40 p-5" style="display:none;" data-register-panel="outdoor">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">{{ __('panel.track_outdoor') }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ __('panel.outdoor_role_help') }}</p>

                    <div id="outdoor-role-fields" class="mt-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('panel.outdoor_role') }}</p>
                        <div class="mt-3 grid gap-2 md:grid-cols-2">
                            <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm">
                                <input type="radio" name="outdoor_role" value="owner" class="ooh-role" @checked(old('outdoor_role', 'owner') === 'owner') onchange="window.toggleOutdoorFields && window.toggleOutdoorFields()">
                                <span class="font-semibold">{{ __('panel.outdoor_role_owner') }}</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm">
                                <input type="radio" name="outdoor_role" value="agency" class="ooh-role" @checked(old('outdoor_role') === 'agency') onchange="window.toggleOutdoorFields && window.toggleOutdoorFields()">
                                <span class="font-semibold">{{ __('panel.outdoor_role_agency') }}</span>
                            </label>
                        </div>
                        @error('outdoor_role')<div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>@enderror
                        <div id="owner-kind-fields" class="mt-4" style="display:none;">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('panel.owner_kind') }}</p>
                            <div class="mt-3 grid gap-2 md:grid-cols-2">
                                <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm">
                                    <input type="radio" name="owner_kind" value="company" class="ooh-kind" @checked(old('owner_kind', 'company') === 'company') onchange="window.toggleOutdoorFields && window.toggleOutdoorFields()">
                                    <span class="font-semibold">{{ __('panel.owner_kind_company') }}</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm">
                                    <input type="radio" name="owner_kind" value="municipality" class="ooh-kind" @checked(old('owner_kind') === 'municipality') onchange="window.toggleOutdoorFields && window.toggleOutdoorFields()">
                                    <span class="font-semibold">{{ __('panel.owner_kind_municipality') }}</span>
                                </label>
                            </div>
                            @error('owner_kind')<div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-6 by-divider"></div>
                    <p class="mt-6 text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('panel.location') }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ __('panel.location_help') }}</p>
                    <div class="mt-4">
                        @include('partials.geo-location-fields', [
                            'countryValue' => old('country_code', 'TR'),
                            'cityValue' => old('city', ''),
                            'districtValue' => old('district', ''),
                            'countries' => $countries ?? collect(),
                            'provinces' => $provinces ?? collect(),
                            'trDistricts' => $districts ?? collect(),
                            'citySuggestions' => $citySuggestions ?? [],
                            'districtSuggestions' => $districtSuggestions ?? [],
                            'idPrefix' => 'reg-ooh-geo',
                        ])
                    </div>

                    <div id="outdoor-tax-fields" class="mt-6" style="display:none;">
                        <div class="by-divider mb-4"></div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('panel.tax_info') }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ __('panel.tax_plate_required_help') }}</p>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="form-label">{{ __('panel.company_name') }}</label>
                                <input type="text" class="form-control mt-1" name="company_name" value="{{ old('company_name') }}" data-ooh-field>
                            </div>
                            <div>
                                <label class="form-label">{{ __('panel.tax_office') }}</label>
                                <input type="text" class="form-control mt-1" name="tax_office" value="{{ old('tax_office') }}" data-ooh-field>
                            </div>
                            <div>
                                <label class="form-label">{{ __('panel.tax_number') }}</label>
                                <input type="text" class="form-control mt-1" name="tax_number" value="{{ old('tax_number') }}" data-ooh-field>
                            </div>
                            <div class="md:col-span-2">
                                <label class="form-label">{{ __('panel.tax_plate') }}</label>
                                <input type="file" class="form-control mt-1" name="tax_plate" accept=".pdf,image/*" data-ooh-field>
                                @error('tax_plate')<div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div id="municipality-doc-fields" class="mt-6" style="display:none;">
                        <div class="by-divider mb-4"></div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('panel.doc_municipality_authority') }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ __('panel.municipality_docs_help') }}</p>
                        <div class="mt-4">
                            <input type="file" class="form-control mt-1" name="municipality_authority" accept=".pdf,image/*" data-ooh-field>
                            @error('municipality_authority')<div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-6 by-divider"></div>
                    <div class="mt-4">
                        @include('partials.legal-scroll-gate', [
                            'slug' => 'outdoor_vendor_agreement',
                            'label' => __('panel.vendor_agreement'),
                            'field' => 'accept_vendor_agreement',
                            'contractHtml' => $vendorAgreement->content_html ?? null,
                        ])
                    </div>
                </div>

                <div class="by-surface-indigo p-4 space-y-3">
                    <div class="by-accent-bar mb-3"></div>
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

                <button type="submit" class="w-full by-btn-cta">Kayıt Ol</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Zaten hesabınız var mı? <a href="{{ route('login') }}" class="by-link">Giriş yapın</a>
            </p>
        </div>
    </div>
</div>
<script>
window.setPanelEnabled = function (panel, on) {
    if (!panel) return;
    panel.style.display = on ? 'block' : 'none';
    panel.querySelectorAll('input, select, textarea, button').forEach(function (el) {
        if (el.type === 'hidden' && el.name && el.name.indexOf('_scrolled_at') !== -1) {
            return;
        }
        el.disabled = !on;
    });
};
window.toggleOutdoorFields = function () {
    var roleEl = document.querySelector('#outdoor-extra-fields .ooh-role:checked');
    var kindEl = document.querySelector('#outdoor-extra-fields .ooh-kind:checked');
    var role = roleEl ? roleEl.value : 'owner';
    var kind = kindEl ? kindEl.value : 'company';
    var municipality = role === 'owner' && kind === 'municipality';
    var kindBox = document.getElementById('owner-kind-fields');
    var tax = document.getElementById('outdoor-tax-fields');
    var muni = document.getElementById('municipality-doc-fields');
    if (kindBox) kindBox.style.display = role === 'owner' ? 'block' : 'none';
    if (tax) tax.style.display = !municipality ? 'block' : 'none';
    if (muni) muni.style.display = municipality ? 'block' : 'none';
};
window.toggleVendorTracks = function () {
    var checks = document.querySelectorAll('#vendor-extra-fields .track-cb:checked');
    var tracks = Array.from(checks).map(function (c) { return c.value; });
    var physical = tracks.indexOf('physical_products') >= 0 || tracks.indexOf('physical_quote') >= 0;
    var freelancer = tracks.indexOf('freelancer') >= 0;
    var tax = document.getElementById('physical-tax-fields');
    var docs = document.getElementById('freelancer-doc-fields');
    if (tax) tax.style.display = physical ? 'block' : 'none';
    if (docs) docs.style.display = freelancer ? 'block' : 'none';
};
window.toggleRegisterKind = function () {
    var vendor = document.getElementById('role_vendor');
    var outdoor = document.getElementById('role_outdoor');
    var vendorBox = document.getElementById('vendor-extra-fields');
    var outdoorBox = document.getElementById('outdoor-extra-fields');
    var isVendor = !!(vendor && vendor.checked);
    var isOutdoor = !!(outdoor && outdoor.checked);
    window.setPanelEnabled(vendorBox, isVendor);
    window.setPanelEnabled(outdoorBox, isOutdoor);
    if (isVendor) window.toggleVendorTracks();
    if (isOutdoor) window.toggleOutdoorFields();
};
document.addEventListener('DOMContentLoaded', function () {
    try {
        var params = new URLSearchParams(window.location.search);
        var role = params.get('role');
        if (role === 'vendor' || role === 'outdoor' || role === 'customer') {
            var el = document.getElementById('role_' + role);
            if (el) el.checked = true;
        }
    } catch (e) {}
    window.toggleRegisterKind();
});
</script>
@endsection
