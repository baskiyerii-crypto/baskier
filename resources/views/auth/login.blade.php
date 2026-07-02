@extends('layouts.app')

@section('title', 'Giriş Yap - BaskıYeri')

@section('content')
<div class="by-container py-10">
    <div class="mx-auto max-w-md">
        <div class="mb-6 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Hesabın</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Giriş yap</h1>
            <p class="mt-2 text-sm text-slate-600">Tekliflerin, siparişlerin ve mesajların için.</p>
        </div>

        <div class="by-card p-6 md:p-8">
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $err) <div>{{ $err }}</div> @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="email" class="form-label">E-posta</label>
                    <input type="email" class="form-control mt-1" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div>
                    <label for="password" class="form-label">Şifre</label>
                    <div class="mt-1 flex items-center gap-2">
                        <input type="password" class="form-control flex-1" id="password" name="password" required>
                        <button
                            type="button"
                            class="by-btn-secondary px-4 py-3"
                            id="toggle-password-visibility"
                            aria-label="Şifreyi basılı tutunca göster"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    Beni hatırla
                </label>

                <button type="submit" class="w-full by-btn-cta">Giriş Yap</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Hesabınız yok mu? <a href="{{ route('register') }}" class="by-link">Kayıt olun</a>
            </p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const visibilityButton = document.getElementById('toggle-password-visibility');

        if (!passwordInput || !visibilityButton) {
            return;
        }

        const showPassword = function () {
            passwordInput.type = 'text';
        };

        const hidePassword = function () {
            passwordInput.type = 'password';
        };

        visibilityButton.addEventListener('mousedown', showPassword);
        visibilityButton.addEventListener('mouseup', hidePassword);
        visibilityButton.addEventListener('mouseleave', hidePassword);
        visibilityButton.addEventListener('touchstart', showPassword, { passive: true });
        visibilityButton.addEventListener('touchend', hidePassword);
        visibilityButton.addEventListener('touchcancel', hidePassword);
    });
</script>
@endsection
