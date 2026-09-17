@extends('layouts.app')
@section('title', __('panel.verify_account') . ' - BaskıYeri')
@section('content')
<div class="by-container py-12 md:py-16">
    <div class="mx-auto max-w-lg">
        <div class="mb-6 text-center">
            <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink">{{ __('panel.verify_account') }}</h1>
            <p class="text-xs text-muted mt-1">{{ __('panel.your_id') }}: <strong class="text-ink font-mono">{{ $user->publicCode() }}</strong></p>
            <p class="text-xs text-muted mt-1">{{ __('panel.verify_dual_help') }}</p>
        </div>

        <div class="space-y-4">
            <div class="by-card p-6 bg-surface border border-border">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="font-heading text-sm font-bold text-ink">E-posta Doğrulama</h2>
                    <x-badge :variant="$user->email_verified_at ? 'success' : 'neutral'">
                        {{ $user->email_verified_at ? __('panel.verified') : __('panel.not_verified') }}
                    </x-badge>
                </div>
                <p class="text-xs text-muted mb-4">{{ $user->email }}</p>
                @unless($user->email_verified_at)
                    <form method="post" action="{{ route('otp.send') }}" class="mb-3">
                        @csrf
                        <input type="hidden" name="channel" value="email">
                        <button class="btn btn-secondary text-xs py-1.5 px-3">{{ __('panel.send_otp') }}</button>
                    </form>
                    <form method="post" action="{{ route('otp.verify') }}" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="channel" value="email">
                        <input name="code" maxlength="6" class="form-control text-xs font-mono max-w-[140px]" placeholder="123456" required>
                        <button class="btn btn-cta text-xs py-1.5 px-4">{{ __('panel.confirm') }}</button>
                    </form>
                @endunless
            </div>

            <div class="by-card p-6 bg-surface border border-border">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="font-heading text-sm font-bold text-ink">WhatsApp Doğrulama</h2>
                    <x-badge :variant="$user->phone_verified_at ? 'success' : 'neutral'">
                        {{ $user->phone_verified_at ? __('panel.verified') : __('panel.not_verified') }}
                    </x-badge>
                </div>
                <p class="text-xs text-muted mb-4">{{ $user->phone ?? 'Telefon tanımlanmamış' }}</p>
                @unless($user->phone_verified_at)
                    <form method="post" action="{{ route('otp.send') }}" class="mb-3 space-y-2">
                        @csrf
                        <input type="hidden" name="channel" value="whatsapp">
                        <div class="flex gap-2">
                            <input name="phone" value="{{ old('phone', $user->phone) }}" class="form-control text-xs flex-1" placeholder="+905xxxxxxxxx" required>
                            <button class="btn btn-secondary text-xs py-1.5 px-3 shrink-0">{{ __('panel.send_otp') }}</button>
                        </div>
                    </form>
                    <form method="post" action="{{ route('otp.verify') }}" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="channel" value="whatsapp">
                        <input name="code" maxlength="6" class="form-control text-xs font-mono max-w-[140px]" placeholder="123456" required>
                        <button class="btn btn-cta text-xs py-1.5 px-4">{{ __('panel.confirm') }}</button>
                    </form>
                @endunless
            </div>
        </div>
    </div>
</div>
@endsection
