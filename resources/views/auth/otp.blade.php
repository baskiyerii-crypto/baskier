@extends('layouts.app')
@section('title', __('panel.verify_account'))
@section('content')
<div class="mx-auto max-w-lg py-10 px-4">
    <h1 class="text-xl font-bold mb-2">{{ __('panel.verify_account') }}</h1>
    <p class="text-sm text-slate-600 mb-2">{{ __('panel.your_id') }}: <strong>{{ $user->publicCode() }}</strong></p>
    <p class="text-sm text-slate-600 mb-6">{{ __('panel.verify_dual_help') }}</p>

    <div class="grid gap-4">
        <div class="by-card p-4">
            <h2 class="font-semibold mb-2">Email</h2>
            <p class="text-xs mb-2">{{ $user->email }} — {{ $user->email_verified_at ? __('panel.verified') : __('panel.not_verified') }}</p>
            @unless($user->email_verified_at)
                <form method="post" action="{{ route('otp.send') }}" class="mb-2">@csrf
                    <input type="hidden" name="channel" value="email">
                    <button class="by-btn-secondary text-sm">{{ __('panel.send_otp') }}</button>
                </form>
                <form method="post" action="{{ route('otp.verify') }}" class="flex gap-2">@csrf
                    <input type="hidden" name="channel" value="email">
                    <input name="code" maxlength="6" class="by-input" placeholder="123456" required>
                    <button class="by-btn-primary text-sm">{{ __('panel.confirm') }}</button>
                </form>
            @endunless
        </div>
        <div class="by-card p-4">
            <h2 class="font-semibold mb-2">WhatsApp</h2>
            <p class="text-xs mb-2">{{ $user->phone_verified_at ? __('panel.verified') : __('panel.not_verified') }}</p>
            @unless($user->phone_verified_at)
                <form method="post" action="{{ route('otp.send') }}" class="mb-2 space-y-2">@csrf
                    <input type="hidden" name="channel" value="whatsapp">
                    <input name="phone" value="{{ old('phone', $user->phone) }}" class="by-input" placeholder="+905xxxxxxxxx" required>
                    <button class="by-btn-secondary text-sm">{{ __('panel.send_otp') }}</button>
                </form>
                <form method="post" action="{{ route('otp.verify') }}" class="flex gap-2">@csrf
                    <input type="hidden" name="channel" value="whatsapp">
                    <input name="code" maxlength="6" class="by-input" placeholder="123456" required>
                    <button class="by-btn-primary text-sm">{{ __('panel.confirm') }}</button>
                </form>
            @endunless
        </div>
    </div>
</div>
@endsection
