<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function __construct(
        private EvolutionWhatsAppService $whatsapp
    ) {}

    public function issue(User $user, string $channel, string $destination): OtpVerification
    {
        $code = (string) random_int(100000, 999999);

        $otp = OtpVerification::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'destination' => $destination,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        if ($channel === 'email') {
            Mail::to($destination)->send(new OtpMail($code));
        }

        if ($channel === 'whatsapp') {
            \App\Jobs\SendWhatsAppMessageJob::dispatch($destination, __('panel.otp_whatsapp_message', ['code' => $code]));
        }

        return $otp;
    }

    public function verify(User $user, string $channel, string $code): bool
    {
        $otp = OtpVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otp || ! $otp->isValid($code)) {
            return false;
        }

        $otp->update(['verified_at' => now()]);

        if ($channel === 'email') {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
        if ($channel === 'whatsapp') {
            $user->forceFill(['phone_verified_at' => now()])->save();
        }

        return true;
    }

    public function isFullyVerified(User $user): bool
    {
        return (bool) $user->email_verified_at && (bool) $user->phone_verified_at;
    }
}
