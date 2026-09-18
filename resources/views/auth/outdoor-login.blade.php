@extends('layouts.app')

@php
    $isSaha = ($shell ?? '') === 'saha';
    $appName = $isSaha ? 'Saha BaskıYeri' : 'Outdoor BaskıYeri';
@endphp

@section('title', $appName.' giriş')
@section('manifest', $isSaha ? '/manifest-saha.webmanifest' : '/manifest-outdoor.webmanifest')
@section('theme_color', $isSaha ? '#1e3a5f' : '#059669')

@section('content')
<div class="by-container py-10">
    <div class="mx-auto max-w-md">
        <div class="mb-6 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $appName }}</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Giriş yap</h1>
            <p class="mt-2 text-sm text-slate-600">Telefon numarası ve şifre ile giriş yapın.</p>
        </div>

        <div class="by-card p-6 md:p-8">
            @if(session('info'))
                <div class="mb-4 rounded-lg bg-sky-50 px-3 py-2 text-sm text-sky-800">{{ session('info') }}</div>
            @endif

            <form method="POST" action="{{ $isSaha ? route('saha.login') : route('outdoor.login') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="phone" class="form-label">Telefon</label>
                    <input type="tel" class="form-control mt-1" id="phone" name="phone" value="{{ old('phone') }}" required autofocus placeholder="05xx xxx xx xx">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    Beni hatırla
                </label>

                <button type="submit" class="w-full by-btn-cta">Giriş Yap</button>
            </form>
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
