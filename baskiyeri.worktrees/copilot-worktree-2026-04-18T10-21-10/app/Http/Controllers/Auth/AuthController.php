<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['boolean'],
        ]);

        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], $validated['remember'] ?? false)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (session()->has('pending_quote_request')) {
                $data = session('pending_quote_request');
                $data['user_id'] = $user->id;
                $data['status'] = 'open';
                QuoteRequest::create($data);
                session()->forget('pending_quote_request');
                return redirect()->route('quote-requests.index')->with('success', 'Teklif talebiniz gönderildi. Satıcılar size teklif verebilecek.');
            }

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }
            if ($user->isVendor()) {
                return redirect()->intended(route('vendor.dashboard'));
            }
            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors(['email' => 'E-posta veya şifre hatalı.'])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        $businessTypes = BusinessType::orderBy('sort_order')->orderBy('name')->get();

        return view('auth.register', compact('businessTypes'));
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:customer,vendor'],
            'business_type_ids' => ['nullable', 'array'],
            'business_type_ids.*' => ['exists:business_types,id'],
        ];
        if ($request->input('role') === 'vendor' && BusinessType::query()->exists()) {
            $rules['business_type_ids'] = ['required', 'array', 'min:1'];
        }
        $validated = $request->validate($rules);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        if ($validated['role'] === 'vendor') {
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'slug' => \Illuminate\Support\Str::slug($validated['name']) . '-' . $user->id,
                'email' => $validated['email'],
                'is_active' => false,
            ]);
            $user->update(['vendor_id' => $vendor->id]);
            $vendor->businessTypes()->sync($request->input('business_type_ids', []));
        }

        Auth::login($user);

        if (session()->has('pending_quote_request')) {
            $data = session('pending_quote_request');
            $data['user_id'] = $user->id;
            $data['status'] = 'open';
            QuoteRequest::create($data);
            session()->forget('pending_quote_request');
            return redirect()->route('quote-requests.index')->with('success', 'Teklif talebiniz gönderildi. Satıcılar size teklif verebilecek.');
        }

        if ($user->isVendor()) {
            return redirect()->route('vendor.dashboard');
        }
        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
