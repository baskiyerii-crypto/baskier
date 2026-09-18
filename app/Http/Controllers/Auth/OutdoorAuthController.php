<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OutdoorStaffService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OutdoorAuthController extends Controller
{
    public function __construct(private OutdoorStaffService $staff) {}

    public function showOutdoorForm()
    {
        return view('auth.outdoor-login', ['shell' => 'outdoor']);
    }

    public function showSahaForm()
    {
        return view('auth.outdoor-login', ['shell' => 'saha']);
    }

    public function loginOutdoor(Request $request)
    {
        return $this->phoneLogin($request, 'outdoor');
    }

    public function loginSaha(Request $request)
    {
        return $this->phoneLogin($request, 'saha');
    }

    private function phoneLogin(Request $request, string $shell)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $keys = Phone::lookupKeys($validated['phone']);
        if ($keys === []) {
            return back()->withErrors(['phone' => 'Geçerli bir telefon numarası girin.'])->onlyInput('phone');
        }

        $user = User::query()->whereIn('phone', $keys)->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['phone' => 'Telefon veya şifre hatalı.'])->onlyInput('phone');
        }

        if ($user->is_active === false) {
            return back()->withErrors(['phone' => __('panel.account_disabled')])->onlyInput('phone');
        }

        if ($user->isCustomer() || $user->isAdmin() || ! $this->staff->isOutdoorOperator($user)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Bu giriş yalnızca açık hava hesapları içindir. BaskıYeri e-posta ile girin.',
            ]);
        }

        $isField = $this->staff->isFieldOperator($user);
        if ($shell === 'outdoor' && $isField) {
            return redirect()->route('saha.login')->with('info', 'Saha personeli Saha BaskıYeri uygulamasından girer.');
        }
        if ($shell === 'saha' && ! $isField) {
            return redirect()->route('outdoor.login')->with('info', 'Mecra sahibi ve ajans Outdoor BaskıYeri uygulamasından girer.');
        }

        Auth::login($user, (bool) ($validated['remember'] ?? false));
        $request->session()->regenerate();

        if ($isField) {
            return redirect()->intended(route('outdoor-panel.jobs'));
        }

        return redirect()->intended(route('outdoor-panel.dashboard'));
    }
}
