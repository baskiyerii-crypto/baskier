<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
                DB::transaction(function () use ($data, $user) {
                    $items = is_array($data['items'] ?? null) ? $data['items'] : [];
                    unset($data['items']);

                    $data['user_id'] = $user->id;
                    $data['status'] = 'open';
                    $qr = QuoteRequest::create($data);

                    foreach (array_values($items) as $idx => $it) {
                        $qr->items()->create([
                            'category_id' => $it['category_id'] ?? $qr->category_id,
                            'product_id' => $it['product_id'] ?? null,
                            'quantity' => $it['quantity'] ?? null,
                            'unit' => $it['unit'] ?? null,
                            'spec' => $it['spec'] ?? null,
                            'sort_order' => $idx,
                        ]);
                    }
                });
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
            'accept_terms' => ['accepted'],
            'accept_privacy' => ['accepted'],
            'business_type_ids' => ['nullable', 'array'],
            'business_type_ids.*' => ['exists:business_types,id'],
        ];
        if ($request->input('role') === 'vendor') {
            // Tax info is always required for vendor applications.
            $rules['company_name'] = ['required', 'string', 'max:255'];
            $rules['tax_office'] = ['required', 'string', 'max:255'];
            $rules['tax_number'] = ['required', 'string', 'max:32'];
            $rules['tax_plate'] = ['required', 'file', 'max:12288', 'mimes:pdf,jpg,jpeg,png,webp'];
            $rules['accept_vendor_agreement'] = ['accepted'];

            // Business types required only if configured.
            if (BusinessType::query()->exists()) {
                $rules['business_type_ids'] = ['required', 'array', 'min:1'];
            }
        }
        $validated = $request->validate($rules);

        $user = null;
        DB::transaction(function () use ($request, $validated, &$user) {
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
                    'company_name' => $validated['company_name'],
                    'tax_office' => $validated['tax_office'],
                    'tax_number' => $validated['tax_number'],
                    'slug' => \Illuminate\Support\Str::slug($validated['name']) . '-' . $user->id,
                    'email' => $validated['email'],
                    'is_active' => false,
                    'verification_status' => 'pending',
                ]);
                $user->update(['vendor_id' => $vendor->id]);
                $vendor->businessTypes()->sync($request->input('business_type_ids', []));

                if ($request->hasFile('tax_plate')) {
                    $path = $request->file('tax_plate')->store("vendor-documents/{$vendor->id}", 'public');
                    VendorDocument::create([
                        'vendor_id' => $vendor->id,
                        'document_type' => 'tax_plate',
                        'path' => $path,
                        'status' => 'pending',
                    ]);
                }
            }
        });

        Auth::login($user);

        if (session()->has('pending_quote_request')) {
            $data = session('pending_quote_request');
            DB::transaction(function () use ($data, $user) {
                $items = is_array($data['items'] ?? null) ? $data['items'] : [];
                unset($data['items']);

                $data['user_id'] = $user->id;
                $data['status'] = 'open';
                $qr = QuoteRequest::create($data);

                foreach (array_values($items) as $idx => $it) {
                    $qr->items()->create([
                        'category_id' => $it['category_id'] ?? $qr->category_id,
                        'product_id' => $it['product_id'] ?? null,
                        'quantity' => $it['quantity'] ?? null,
                        'unit' => $it['unit'] ?? null,
                        'spec' => $it['spec'] ?? null,
                        'sort_order' => $idx,
                    ]);
                }
            });
            session()->forget('pending_quote_request');
            return redirect()->route('quote-requests.index')->with('success', 'Teklif talebiniz gönderildi. Satıcılar size teklif verebilecek.');
        }

        if ($user->isVendor()) {
            return redirect()->route('vendor.dashboard')->with('info', 'Satıcı başvurunuz alındı. Vergi bilgileriniz ve levhanız admin onayından sonra paneliniz aktif olur.');
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
