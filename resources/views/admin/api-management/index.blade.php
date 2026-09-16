@extends('layouts.admin')
@section('title', 'API Yönetimi')
@section('content')
<div class="card p-4" style="max-width:720px;">
    <form method="post" action="{{ route('admin.api-management.update') }}">
        @csrf
        <h2 class="h6">Ödeme sağlayıcısı</h2>
        <div class="mb-3">
            <select name="payment_provider" class="form-select">
                <option value="shopify" @selected(($payment_provider ?? 'shopify')==='shopify')>Shopify (varsayılan)</option>
                <option value="iyzico" @selected(($payment_provider ?? '')==='iyzico')>iyzico</option>
            </select>
            <div class="form-text">İlk etap Shopify; istediğiniz zaman iyzico’ya geçebilirsiniz.</div>
        </div>
        <h2 class="h6 mt-3">Shopify</h2>
        <div class="row g-3 mb-3">
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
        <h2 class="h6">iyzico</h2>
        <div class="row g-3 mb-3">
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
                <input type="password" name="iyzico_secret_key" class="form-control" value="{{ $iyzico_secret_key }}">
            </div>
        </div>
        <h2 class="h6 mt-4">Basit Kargo</h2>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">API Key</label>
                <input type="text" name="basitkargo_api_key" class="form-control" value="{{ $basitkargo_api_key }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Base URL</label>
                <input type="url" name="basitkargo_base_url" class="form-control" value="{{ $basitkargo_base_url }}">
            </div>
        </div>
        <h2 class="h6 mt-4">OpenAI (blog insanlaştırma)</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <label class="form-label">API Key</label>
                <input type="password" name="openai_api_key" class="form-control" value="{{ $openai_api_key }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Model</label>
                <input type="text" name="openai_model" class="form-control" value="{{ $openai_model }}">
            </div>
        </div>
        <h2 class="h6 mt-4">Evolution WhatsApp (OTP)</h2>
        <p class="small text-muted">Proje içi <code>docker compose up -d evolution</code> ile ayağa kalkar.</p>
        <div class="row g-3 mb-4">
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
        <button class="btn btn-primary">{{ __('panel.save') }}</button>
    </form>
</div>
@endsection
