@extends('layouts.app')

@section('title', 'Giriş Yap - BaskıYeri')

@section('content')
<div class="by-container py-12 md:py-16">
    <div class="mx-auto max-w-md">
        <div class="mb-6 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-muted">BaskıYeri Hesabınız</p>
            <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink mt-1">Giriş Yap</h1>
            <p class="mt-1.5 text-xs text-muted">Siparişleriniz, teklifleriniz ve atölyeniz için oturum açın.</p>
        </div>

        <div class="by-card p-6 md:p-8 bg-surface border border-border">
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-muted mb-1">E-posta Adresi <span class="text-red-500">*</span></label>
                    <input type="email" class="form-control text-xs" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="ornek@baskiyeri.com">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-xs font-semibold text-muted">Şifre <span class="text-red-500">*</span></label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="password" class="form-control text-xs flex-1" id="password" name="password" required placeholder="••••••••">
                        <button
                            type="button"
                            class="btn btn-secondary text-xs px-3 py-2"
                            id="toggle-password-visibility"
                            aria-label="Şifreyi basılı tutunca göster"
                            title="Basılı tutarak şifreyi gör"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-xs text-ink cursor-pointer">
                        <input type="checkbox" class="h-4 w-4 rounded border-border text-cta focus:ring-cta" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Beni hatırla</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-cta w-full text-xs py-2.5 font-bold">Giriş Yap</button>
            </form>

            <div class="mt-6 pt-6 border-t border-border text-center text-xs text-muted">
                Henüz hesabınız yok mu? <a href="{{ route('register') }}" class="font-bold text-cta hover:underline">Hemen Ücretsiz Kayıt Olun</a>
            </div>
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
