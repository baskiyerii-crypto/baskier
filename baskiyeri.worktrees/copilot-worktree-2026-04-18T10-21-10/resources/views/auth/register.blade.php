@extends('layouts.app')

@section('title', 'Kayıt Ol - BaskıYeri')

@section('content')
<div class="content-shell py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="bg-white rounded-4 shadow-sm p-4 p-md-5">
                <h1 class="h4 mb-4">Kayıt Ol</h1>
                @if (session('info'))
                    <div class="alert alert-info small">{{ session('info') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger small">
                        @foreach ($errors->all() as $err) {{ $err }} @endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold">Ad Soyad</label>
                        <input type="text" class="form-control rounded-3" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">E-posta</label>
                        <input type="email" class="form-control rounded-3" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold">Şifre</label>
                        <input type="password" class="form-control rounded-3" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label small fw-semibold">Şifre (tekrar)</label>
                        <input type="password" class="form-control rounded-3" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Hesap türü</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="role_customer" name="role" value="customer" {{ old('role', 'customer') === 'customer' ? 'checked' : '' }} onchange="window.toggleVendorBiz && window.toggleVendorBiz()">
                                <label class="form-check-label small" for="role_customer">Müşteri</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="role_vendor" name="role" value="vendor" {{ old('role') === 'vendor' ? 'checked' : '' }} onchange="window.toggleVendorBiz && window.toggleVendorBiz()">
                                <label class="form-check-label small" for="role_vendor">Satıcı</label>
                            </div>
                        </div>
                    </div>
                    @if(isset($businessTypes) && $businessTypes->isNotEmpty())
                        <div id="vendor-business-fields" class="mb-4 border rounded-3 p-3 bg-light" style="display:none;">
                            <label class="form-label small fw-semibold d-block mb-2">Satıcı iş kolu (en az birini seçin)</label>
                            <div class="row g-2">
                                @foreach($businessTypes as $bt)
                                    <div class="col-12 col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="business_type_ids[]" id="bt{{ $bt->id }}" value="{{ $bt->id }}"
                                                @checked(in_array($bt->id, old('business_type_ids', [])))>
                                            <label class="form-check-label small" for="bt{{ $bt->id }}">{{ $bt->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('business_type_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                    @endif
                    <button type="submit" class="btn btn-warning w-100 rounded-3 fw-semibold">Kayıt Ol</button>
                </form>
                <p class="small text-muted mt-4 mb-0 text-center">
                    Zaten hesabınız var mı? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Giriş yapın</a>
                </p>
            </div>
        </div>
    </div>
</div>
<script>
window.toggleVendorBiz = function () {
    var v = document.getElementById('role_vendor');
    var box = document.getElementById('vendor-business-fields');
    if (!box) return;
    box.style.display = v && v.checked ? 'block' : 'none';
};
document.addEventListener('DOMContentLoaded', function () { window.toggleVendorBiz(); });
</script>
@endsection
