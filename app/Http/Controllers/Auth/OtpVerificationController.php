<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;

class OtpVerificationController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return view('auth.otp', compact('user'));
    }

    public function send(Request $request, OtpService $otp)
    {
        $validated = $request->validate([
            'channel' => ['required', 'in:email,whatsapp'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $user = $request->user();
        if ($validated['channel'] === 'whatsapp') {
            if ($request->filled('phone')) {
                $user->update(['phone' => $validated['phone']]);
            }
            abort_unless($user->phone, 422, __('panel.phone_required'));
            $otp->issue($user, 'whatsapp', $user->phone);
        } else {
            $otp->issue($user, 'email', $user->email);
        }

        return back()->with('success', __('panel.otp_sent'));
    }

    public function verify(Request $request, OtpService $otp)
    {
        $validated = $request->validate([
            'channel' => ['required', 'in:email,whatsapp'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $ok = $otp->verify($request->user(), $validated['channel'], $validated['code']);
        if (! $ok) {
            return back()->with('error', __('panel.otp_invalid'));
        }

        return back()->with('success', __('panel.otp_verified'));
    }
}
