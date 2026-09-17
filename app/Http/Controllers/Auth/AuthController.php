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
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

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

            if ($user->is_active === false) {
                Auth::logout();

                return back()->withErrors(['email' => __('panel.account_disabled')])->onlyInput('email');
            }

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

                return redirect()->route('quote-requests.index')->with('success', __('panel.quote_request_sent'));
            }

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }
            if ($user->isVendor()) {
                return redirect()->intended(route('vendor.dashboard'));
            }

            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors(['email' => __('panel.login_failed')])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        $businessTypes = BusinessType::orderBy('sort_order')->orderBy('name')->get();
        $termsContract = \App\Models\Contract::query()->where('key', 'terms')->where('is_active', true)->first();
        $privacyContract = \App\Models\Contract::query()->where('key', 'privacy')->where('is_active', true)->first();
        $vendorAgreement = \App\Models\Contract::query()->where('key', 'vendor_agreement')->where('is_active', true)->first();

        return view('auth.register', compact('businessTypes', 'termsContract', 'privacyContract', 'vendorAgreement'));
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
            'accept_terms_scrolled_at' => ['required', 'date'],
            'accept_privacy_scrolled_at' => ['required', 'date'],
            'business_type_ids' => ['nullable', 'array'],
            'business_type_ids.*' => ['exists:business_types,id'],
            'registration_tracks' => ['nullable', 'array'],
            'registration_tracks.*' => ['in:physical_products,physical_quote,freelancer,outdoor'],
            'freelancer_docs' => ['nullable', 'array'],
            'freelancer_docs.*' => ['file', 'max:12288', 'mimes:pdf,jpg,jpeg,png,webp'],
            'freelancer_doc_types' => ['nullable', 'array'],
            'freelancer_doc_types.*' => ['in:certificate,diploma,course,other'],
        ];

        if ($request->input('role') === 'vendor') {
            $tracks = array_values(array_unique($request->input('registration_tracks', [])));
            $rules['registration_tracks'] = ['required', 'array', 'min:1'];
            $rules['accept_vendor_agreement'] = ['accepted'];
            $rules['accept_vendor_agreement_scrolled_at'] = ['required', 'date'];

            $needsPhysical = in_array('physical_products', $tracks, true)
                || in_array('physical_quote', $tracks, true)
                || in_array('outdoor', $tracks, true);
            $freelancerOnly = in_array('freelancer', $tracks, true) && ! $needsPhysical;

            if ($needsPhysical) {
                $rules['company_name'] = ['required', 'string', 'max:255'];
                $rules['tax_office'] = ['required', 'string', 'max:255'];
                $rules['tax_number'] = ['required', 'string', 'max:32'];
                $rules['tax_plate'] = ['required', 'file', 'max:12288', 'mimes:pdf,jpg,jpeg,png,webp'];
            } else {
                $rules['company_name'] = ['nullable', 'string', 'max:255'];
                $rules['tax_office'] = ['nullable', 'string', 'max:255'];
                $rules['tax_number'] = ['nullable', 'string', 'max:32'];
            }

            if ($freelancerOnly || in_array('freelancer', $tracks, true)) {
                $rules['freelancer_docs'] = ['required', 'array', 'min:1'];
            }

            if (in_array('outdoor', $tracks, true)) {
                $rules['outdoor_permit'] = ['required', 'file', 'max:12288', 'mimes:pdf,jpg,jpeg,png,webp'];
            }

            if (BusinessType::query()->exists()) {
                $rules['business_type_ids'] = ['required', 'array', 'min:1'];
            }
        }

        $validated = $request->validate($rules);

        if (($validated['role'] ?? '') === 'vendor') {
            $tracks = array_values(array_unique($validated['registration_tracks'] ?? []));
            if ($tracks === []) {
                throw ValidationException::withMessages([
                    'registration_tracks' => __('panel.select_at_least_one_track'),
                ]);
            }
        }

        $user = null;
        DB::transaction(function () use ($request, $validated, &$user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'is_active' => true,
            ]);

            if ($validated['role'] === 'vendor') {
                $tracks = array_values(array_unique($validated['registration_tracks'] ?? []));
                $needsPhysical = in_array('physical_products', $tracks, true)
                    || in_array('physical_quote', $tracks, true)
                    || in_array('outdoor', $tracks, true);

                $vendor = Vendor::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'company_name' => $validated['company_name'] ?? $validated['name'],
                    'tax_office' => $validated['tax_office'] ?? null,
                    'tax_number' => $validated['tax_number'] ?? null,
                    'slug' => \Illuminate\Support\Str::slug($validated['name']).'-'.$user->id,
                    'email' => $validated['email'],
                    'is_active' => false,
                    'verification_status' => 'pending',
                    'registration_tracks' => $tracks,
                    'freelancer_enabled' => in_array('freelancer', $tracks, true),
                    'quotes_enabled' => in_array('physical_quote', $tracks, true),
                    'outdoor_enabled' => false,
                ]);
                $user->update(['vendor_id' => $vendor->id]);
                $vendor->businessTypes()->sync($request->input('business_type_ids', []));
                if (in_array('outdoor', $tracks, true)) {
                    app(\App\Services\OutdoorStaffService::class)->ensureOwner($vendor);
                }

                $storageService = app(\App\Services\DocumentStorageService::class);

                if ($needsPhysical && $request->hasFile('tax_plate')) {
                    try {
                        $stored = $storageService->storeUploadedDocument($request->file('tax_plate'), $vendor->id);
                        VendorDocument::create([
                            'vendor_id' => $vendor->id,
                            'document_type' => 'tax_plate',
                            'disk' => $stored['disk'],
                            'file_path' => $stored['file_path'],
                            'path' => $stored['file_path'],
                            'original_filename' => $stored['original_filename'],
                            'mime_type' => $stored['mime_type'],
                            'file_size' => $stored['file_size'],
                            'quarantine_status' => $stored['quarantine_status'],
                            'status' => 'pending',
                        ]);
                    } catch (\Throwable $e) {
                        // ignore upload errors during registration to avoid blocking account creation
                    }
                }

                if (in_array('outdoor', $tracks, true) && $request->hasFile('outdoor_permit')) {
                    try {
                        $stored = $storageService->storeUploadedDocument($request->file('outdoor_permit'), $vendor->id);
                        VendorDocument::create([
                            'vendor_id' => $vendor->id,
                            'document_type' => 'outdoor_permit',
                            'disk' => $stored['disk'],
                            'file_path' => $stored['file_path'],
                            'path' => $stored['file_path'],
                            'original_filename' => $stored['original_filename'],
                            'mime_type' => $stored['mime_type'],
                            'file_size' => $stored['file_size'],
                            'quarantine_status' => $stored['quarantine_status'],
                            'status' => 'pending',
                        ]);
                    } catch (\Throwable $e) {
                    }
                }

                $files = $request->file('freelancer_docs', []);
                $types = $request->input('freelancer_doc_types', []);
                foreach ($files as $i => $file) {
                    if (! $file) {
                        continue;
                    }
                    try {
                        $stored = $storageService->storeUploadedDocument($file, $vendor->id);
                        VendorDocument::create([
                            'vendor_id' => $vendor->id,
                            'document_type' => $types[$i] ?? 'certificate',
                            'disk' => $stored['disk'],
                            'file_path' => $stored['file_path'],
                            'path' => $stored['file_path'],
                            'original_filename' => $stored['original_filename'],
                            'mime_type' => $stored['mime_type'],
                            'file_size' => $stored['file_size'],
                            'quarantine_status' => $stored['quarantine_status'],
                            'status' => 'pending',
                        ]);
                    } catch (\Throwable $e) {
                        // ignore upload errors during registration
                    }
                }
            }
        });

        Auth::login($user);

        if ($user->isVendor()) {
            return redirect()->route('vendor.dashboard')->with('info', __('panel.vendor_application_received'));
        }

        return redirect()->route('otp.show')->with('success', __('panel.verify_dual_help'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
