@extends('layouts.admin')
@section('title', __('panel.nav_api'))
@section('content')
<style>
    .api-card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; background: #fff; }
    .api-card-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: .75rem; }
    .api-card-head h2 { margin: 0; font-size: 1rem; font-weight: 700; }
    .form-switch .form-check-input { width: 2.75rem; height: 1.4rem; cursor: pointer; }
    .api-fields[data-disabled="1"] { opacity: .55; pointer-events: none; }
</style>
<div style="max-width:820px;">
    <p class="text-muted small mb-3">Her entegrasyonu bağımsız aç/kapa. Kapalıysa ilgili servis çağrı yapılmaz.</p>
    <form method="post" action="{{ route('admin.api-management.update') }}" id="api-mgmt-form">
        @csrf

        <div class="api-card">
            <div class="api-card-head">
                <h2>Ödeme sağlayıcısı (tercih)</h2>
            </div>
            <select name="payment_provider" class="form-select" style="max-width:280px;">
                <option value="shopify" @selected(($payment_provider ?? 'shopify')==='shopify')>Shopify (varsayılan)</option>
                <option value="iyzico" @selected(($payment_provider ?? '')==='iyzico')>iyzico</option>
            </select>
            <div class="form-text">Tercih edilen kapalıysa diğer açık sağlayıcıya düşülür.</div>
        </div>

        <div class="api-card" data-api="shopify">
            <div class="api-card-head">
                <h2>Shopify</h2>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input api-toggle" type="checkbox" role="switch" name="api_shopify_enabled" value="1" id="api_shopify" @checked($shopify_enabled)>
                    <label class="form-check-label" for="api_shopify">{{ $shopify_enabled ? 'Açık' : 'Kapalı' }}</label>
                </div>
            </div>
            <div class="api-fields row g-3" data-disabled="{{ $shopify_enabled ? '0' : '1' }}">
                <div class="col-md-6">
                    <label class="form-label">Shop domain</label>
                    <input type="text" name="shopify_shop_domain" class="form-control" placeholder="magaza.myshopify.com" value="{{ $shopify_shop_domain ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Admin API token</label>
                    <input type="password" name="shopify_admin_token" class="form-control" value="{{ $shopify_admin_token ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">API version</label>
                    <input type="text" name="shopify_api_version" class="form-control" value="{{ $shopify_api_version ?? '2024-01' }}">
                </div>
            </div>
        </div>

        <div class="api-card" data-api="iyzico">
            <div class="api-card-head">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="m-0">iyzico</h2>
                    <span class="badge bg-{{ $iyzico_mode === 'live' ? 'danger' : 'warning text-dark' }}">{{ strtoupper($iyzico_mode) }}</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input api-toggle" type="checkbox" role="switch" name="api_iyzico_enabled" value="1" id="api_iyzico" @checked($iyzico_enabled)>
                        <label class="form-check-label" for="api_iyzico">{{ $iyzico_enabled ? 'Açık' : 'Kapalı' }}</label>
                    </div>
                </div>
            </div>
            <div class="api-fields row g-3" data-disabled="{{ $iyzico_enabled ? '0' : '1' }}">
                <div class="col-md-4">
                    <label class="form-label">Mod</label>
                    <select name="iyzico_mode" class="form-select">
                        <option value="sandbox" @selected($iyzico_mode==='sandbox')>Test (sandbox)</option>
                        <option value="live" @selected($iyzico_mode==='live')>Canlı</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Base URL</label>
                    <input type="url" name="iyzico_base_url" class="form-control" value="{{ $iyzico_base_url }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">API Key</label>
                    <input type="text" name="iyzico_api_key" class="form-control" value="{{ $iyzico_api_key }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Secret Key</label>
                    <input type="password" name="iyzico_secret_key" class="form-control" value="{{ $iyzico_secret_key }}" placeholder="Değiştirmek için yeni anahtar girin">
                </div>
            </div>
        </div>

        <div class="api-card" data-api="basitkargo">
            <div class="api-card-head">
                <h2>Basit Kargo</h2>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input api-toggle" type="checkbox" role="switch" name="api_basitkargo_enabled" value="1" id="api_basitkargo" @checked($basitkargo_enabled)>
                    <label class="form-check-label" for="api_basitkargo">{{ $basitkargo_enabled ? 'Açık' : 'Kapalı' }}</label>
                </div>
            </div>
            <div class="api-fields row g-3" data-disabled="{{ $basitkargo_enabled ? '0' : '1' }}">
                <div class="col-md-6">
                    <label class="form-label">API Key</label>
                    <input type="text" name="basitkargo_api_key" class="form-control" value="{{ $basitkargo_api_key }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Base URL</label>
                    <input type="url" name="basitkargo_base_url" class="form-control" value="{{ $basitkargo_base_url }}">
                </div>
            </div>
        </div>

        <div class="api-card" data-api="openai">
            <div class="api-card-head">
                <h2>OpenAI (blog insanlaştırma)</h2>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input api-toggle" type="checkbox" role="switch" name="api_openai_enabled" value="1" id="api_openai" @checked($openai_enabled)>
                    <label class="form-check-label" for="api_openai">{{ $openai_enabled ? 'Açık' : 'Kapalı' }}</label>
                </div>
            </div>
            <div class="api-fields row g-3" data-disabled="{{ $openai_enabled ? '0' : '1' }}">
                <div class="col-md-8">
                    <label class="form-label">API Key</label>
                    <input type="password" name="openai_api_key" class="form-control" value="{{ $openai_api_key }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Model</label>
                    <input type="text" name="openai_model" class="form-control" value="{{ $openai_model }}">
                </div>
            </div>
        </div>

        <div class="api-card" data-api="evolution">
            <div class="api-card-head">
                <h2>Evolution WhatsApp (OTP)</h2>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input api-toggle" type="checkbox" role="switch" name="api_evolution_enabled" value="1" id="api_evolution" @checked($evolution_enabled)>
                    <label class="form-check-label" for="api_evolution">{{ $evolution_enabled ? 'Açık' : 'Kapalı' }}</label>
                </div>
            </div>
            <p class="small text-muted mb-2">Proje içi <code>docker compose up -d evolution</code> ile ayağa kalkar.</p>
            <div class="api-fields row g-3" data-disabled="{{ $evolution_enabled ? '0' : '1' }}">
                <div class="col-md-6">
                    <label class="form-label">Base URL</label>
                    <input type="text" name="evolution_base_url" class="form-control" value="{{ $evolution_base_url }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">API Key</label>
                    <input type="password" name="evolution_api_key" class="form-control" value="{{ $evolution_api_key }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Instance</label>
                    <input type="text" name="evolution_instance" class="form-control" value="{{ $evolution_instance }}">
                </div>
            </div>
        </div>

        <button class="btn btn-primary">{{ __('panel.save') }}</button>
    </form>
</div>
<script>
document.querySelectorAll('.api-toggle').forEach(function (el) {
    el.addEventListener('change', function () {
        var card = el.closest('.api-card');
        var fields = card.querySelector('.api-fields');
        var label = card.querySelector('label[for="' + el.id + '"]');
        if (fields) fields.setAttribute('data-disabled', el.checked ? '0' : '1');
        if (label) label.textContent = el.checked ? 'Açık' : 'Kapalı';
    });
});
</script>
@endsection
