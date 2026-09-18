@extends('layouts.admin')
@section('title', __('panel.nav_settings'))
@section('content')
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <h2 class="h6 fw-bold mb-3">{{ __('panel.settings_finance') }}</h2>
            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Komisyon oranı (%)</label>
                    <input type="number" name="commission_rate" class="form-control" value="{{ old('commission_rate', $commission_rate) }}" min="0" max="100" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Hakediş dağılım günü (ayın kaçı)</label>
                    <input type="number" name="payout_day_of_month" class="form-control" value="{{ old('payout_day_of_month', $payout_day_of_month) }}" min="1" max="28" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Para çekme günleri</label>
                    @php $selectedDays = old('payout_weekdays', $payout_weekdays ?? [1,2,3,4,5]); @endphp
                    <div class="d-flex flex-wrap gap-3 small">
                        @foreach([1=>'Pzt',2=>'Sal',3=>'Çar',4=>'Per',5=>'Cum',6=>'Cmt',7=>'Paz'] as $num => $label)
                            <label class="form-check mb-0">
                                <input type="checkbox" class="form-check-input" name="payout_weekdays[]" value="{{ $num }}" @checked(in_array($num, (array) $selectedDays, false) || in_array((string)$num, (array) $selectedDays, true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Asgari çekim tutarı (₺)</label>
                    <input type="number" name="payout_min_amount" class="form-control" value="{{ old('payout_min_amount', $payout_min_amount) }}" min="1" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Unutulan bakiyenin otomatik talebe dönmesi (gün)</label>
                    <input type="number" name="payout_auto_after_days" class="form-control" value="{{ old('payout_auto_after_days', $payout_auto_after_days) }}" min="1" max="90" required>
                    <div class="form-text">Kayıtlı IBAN varsa, çekilmeyen bakiye bu süre sonunda yönetici kuyruğuna düşer.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Komisyon bekleme süresi (gün)</label>
                    <input type="number" name="commission_wait_days" class="form-control" value="{{ old('commission_wait_days', $commission_wait_days) }}" min="0" max="90" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('panel.contract_acceptance_days') }}</label>
                    <input type="number" name="contract_acceptance_days" class="form-control" value="{{ old('contract_acceptance_days', $contract_acceptance_days) }}" min="1" max="90" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tabela görüşme ücreti (₺)</label>
                    <input type="number" name="meeting_fee" class="form-control" value="{{ old('meeting_fee', $meeting_fee) }}" min="0" max="1000" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Freelancer modülü aylık ücreti (₺)</label>
                    <input type="number" name="freelancer_monthly_fee" class="form-control" value="{{ old('freelancer_monthly_fee', $freelancer_monthly_fee) }}" min="0" max="100000" step="0.01" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Teklif verme modülü aylık ücreti (₺)</label>
                    <input type="number" name="quotes_monthly_fee" class="form-control" value="{{ old('quotes_monthly_fee', $quotes_monthly_fee) }}" min="0" max="100000" step="0.01" required>
                </div>

                <hr class="my-4">
                <h2 class="h6 fw-bold mb-3">{{ __('panel.settings_platform') }}</h2>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Site adı</label>
                    <input type="text" name="platform_name" class="form-control" value="{{ old('platform_name', $platform_name) }}" required maxlength="80">
                </div>
                @if(\App\Support\PlatformBranding::logoUrl())
                    <div class="mb-2"><img src="{{ \App\Support\PlatformBranding::logoUrl() }}" alt="logo" style="max-height:48px"></div>
                @endif
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('panel.platform_logo') }}</label>
                    <input type="file" name="platform_logo" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('panel.address') }}</label>
                    <input type="text" name="platform_address" class="form-control" value="{{ old('platform_address', $platform_address) }}">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('panel.phone') }}</label>
                        <input type="text" name="platform_phone" class="form-control" value="{{ old('platform_phone', $platform_phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('panel.email') }}</label>
                        <input type="email" name="platform_email" class="form-control" value="{{ old('platform_email', $platform_email) }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('panel.map_embed') }}</label>
                    <input type="text" name="platform_map_embed_url" class="form-control" value="{{ old('platform_map_embed_url', $platform_map_embed_url) }}">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lat</label>
                        <input type="text" name="platform_map_lat" class="form-control" value="{{ old('platform_map_lat', $platform_map_lat) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lng</label>
                        <input type="text" name="platform_map_lng" class="form-control" value="{{ old('platform_map_lng', $platform_map_lng) }}">
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Instagram</label>
                        <input type="text" name="platform_social_instagram" class="form-control" value="{{ old('platform_social_instagram', $platform_social_instagram) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Website</label>
                        <input type="url" name="platform_social_website" class="form-control" value="{{ old('platform_social_website', $platform_social_website) }}">
                    </div>
                </div>

                <hr class="my-4">
                <h2 class="h6 fw-bold mb-3">Yüzen iletişim butonları</h2>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">WhatsApp numarası</label>
                        <input type="text" name="whatsapp_number" class="form-control" placeholder="905xxxxxxxxx" value="{{ old('whatsapp_number', $whatsapp_number) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Arama numarası</label>
                        <input type="text" name="call_number" class="form-control" value="{{ old('call_number', $call_number) }}">
                    </div>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" name="float_whatsapp_enabled" value="1" id="fw" @checked(old('float_whatsapp_enabled', $float_whatsapp_enabled) == '1')>
                    <label class="form-check-label" for="fw">WhatsApp yüzen buton</label>
                </div>
                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" name="float_call_enabled" value="1" id="fc" @checked(old('float_call_enabled', $float_call_enabled) == '1')>
                    <label class="form-check-label" for="fc">Ara yüzen buton</label>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Ozalit modülü aylık ücreti (₺)</label>
                    <input type="number" name="ozalit_monthly_fee" class="form-control" value="{{ old('ozalit_monthly_fee', $ozalit_monthly_fee ?? 199) }}" min="0" max="100000" step="0.01">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Açık hava (OOH) aylık ücreti (₺)</label>
                    <input type="number" name="outdoor_monthly_fee" class="form-control" value="{{ old('outdoor_monthly_fee', $outdoor_monthly_fee ?? 249) }}" min="0" max="100000" step="0.01">
                </div>

                <hr class="my-4">
                <h2 class="h6 fw-bold mb-3">Yasal kimlik (sözleşme şablonları)</h2>
                <div class="mb-2"><input name="legal_company_title" class="form-control" placeholder="Unvan" value="{{ old('legal_company_title', $legal_company_title ?? '') }}"></div>
                <div class="mb-2"><input name="legal_address" class="form-control" placeholder="Adres" value="{{ old('legal_address', $legal_address ?? '') }}"></div>
                <div class="row g-2 mb-2">
                    <div class="col-md-6"><input name="legal_tax_office" class="form-control" placeholder="Vergi dairesi" value="{{ old('legal_tax_office', $legal_tax_office ?? '') }}"></div>
                    <div class="col-md-6"><input name="legal_tax_number" class="form-control" placeholder="Vergi no" value="{{ old('legal_tax_number', $legal_tax_number ?? '') }}"></div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-6"><input name="legal_mersis" class="form-control" placeholder="MERSİS" value="{{ old('legal_mersis', $legal_mersis ?? '') }}"></div>
                    <div class="col-md-6"><input name="legal_kep" class="form-control" placeholder="KEP" value="{{ old('legal_kep', $legal_kep ?? '') }}"></div>
                </div>
                <div class="row g-2 mb-4">
                    <div class="col-md-6"><input name="legal_email" type="email" class="form-control" placeholder="Yasal e-posta" value="{{ old('legal_email', $legal_email ?? '') }}"></div>
                    <div class="col-md-6"><input name="legal_phone" class="form-control" placeholder="Yasal telefon" value="{{ old('legal_phone', $legal_phone ?? '') }}"></div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">{{ __('panel.save') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
