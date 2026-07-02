@extends('layouts.app')

@section('title', 'Giriş Yap - BaskıYeri')

@section('content')
<div class="content-shell py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="bg-white rounded-4 shadow-sm p-4 p-md-5">
                <h1 class="h4 mb-4">Giriş Yap</h1>
                @if ($errors->any())
                    <div class="alert alert-danger small">
                        @foreach ($errors->all() as $err) {{ $err }} @endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">E-posta</label>
                        <input type="email" class="form-control rounded-3" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold">Şifre</label>
                        <input type="password" class="form-control rounded-3" id="password" name="password" required>
                    </div>
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="remember">Beni hatırla</label>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 rounded-3 fw-semibold">Giriş Yap</button>
                </form>
                <p class="small text-muted mt-4 mb-0 text-center">
                    Hesabınız yok mu? <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Kayıt olun</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
