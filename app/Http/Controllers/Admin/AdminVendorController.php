<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminVendorController extends Controller
{
    public function index(Request $request)
    {
        $allVendors = Vendor::orderBy('name')->get(['id', 'name']);
        $vendors = Vendor::withCount('products')
            ->with(['user', 'businessTypes'])
            ->when($request->filled('vendor_ids'), function ($q) use ($request) {
                $ids = array_filter((array) $request->input('vendor_ids'));
                if ($ids) {
                    $q->whereIn('id', $ids);
                }
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u->where('public_id', 'like', str_replace('%', '', $term).'%')
                            ->orWhere('name', 'like', $term));
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.vendors.index', compact('vendors', 'allVendors'));
    }

    public function show(Vendor $vendor, \App\Services\VendorRiskService $riskService)
    {
        $riskService->recalculate($vendor);
        $vendor->load(['user', 'businessTypes', 'documents', 'products.category']);
        $orders = $vendor->orders()->with('user')->latest()->paginate(15, ['*'], 'orders_page');
        $transactions = $vendor->balanceTransactions()->latest()->paginate(15, ['*'], 'tx_page');

        return view('admin.vendors.show', compact('vendor', 'orders', 'transactions'));
    }

    public function create()
    {
        $businessTypes = BusinessType::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.vendors.create', compact('businessTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'freelancer_enabled' => ['boolean'],
            'freelancer_expires_at' => ['nullable', 'date'],
            'quotes_enabled' => ['boolean'],
            'quotes_expires_at' => ['nullable', 'date'],
            'business_types' => ['nullable', 'array'],
            'business_types.*' => ['exists:business_types,id'],
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'vendor',
        ]);
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('vendors', 'public');
        }
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']) . '-' . $user->id,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'city' => $validated['city'] ?? null,
            'district' => $validated['district'] ?? null,
            'address' => $validated['address'] ?? null,
            'logo' => $logoPath,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'freelancer_enabled' => $request->boolean('freelancer_enabled'),
            'freelancer_expires_at' => $validated['freelancer_expires_at'] ?? null,
            'quotes_enabled' => $request->boolean('quotes_enabled'),
            'quotes_expires_at' => $validated['quotes_expires_at'] ?? null,
        ]);
        $user->update(['vendor_id' => $vendor->id]);
        $vendor->businessTypes()->sync($request->input('business_types', []));
        return redirect()->route('admin.vendors.index')->with('success', 'Satıcı eklendi.');
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load('businessTypes');
        $quoteCategories = \App\Models\Category::orderBy('name')->get();
        $businessTypes = BusinessType::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.vendors.edit', compact('vendor', 'quoteCategories', 'businessTypes'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_office' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'freelancer_enabled' => ['boolean'],
            'freelancer_expires_at' => ['nullable', 'date'],
            'quotes_enabled' => ['boolean'],
            'quotes_expires_at' => ['nullable', 'date'],
            'quote_categories' => ['nullable', 'array'],
            'quote_categories.*' => ['exists:categories,id'],
            'business_types' => ['nullable', 'array'],
            'business_types.*' => ['exists:business_types,id'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . $vendor->id;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['freelancer_enabled'] = $request->boolean('freelancer_enabled');
        $validated['quotes_enabled'] = $request->boolean('quotes_enabled');
        $vendor->quoteCategories()->sync($request->input('quote_categories', []));
        $vendor->businessTypes()->sync($request->input('business_types', []));
        if ($request->hasFile('logo')) {
            if ($vendor->logo) {
                Storage::disk('public')->delete($vendor->logo);
            }
            $validated['logo'] = $request->file('logo')->store('vendors', 'public');
        }
        unset($validated['quote_categories'], $validated['business_types']);
        $vendor->update($validated);
        if ($vendor->user) {
            $vendor->user->update(['name' => $validated['name'], 'email' => $validated['email']]);
        }
        return redirect()->route('admin.vendors.index')->with('success', 'Satıcı güncellendi.');
    }

    public function approveVerification(Vendor $vendor)
    {
        $vendor->update([
            'verification_status' => 'verified',
            'is_active' => true,
        ]);
        $vendor->documents()->where('document_type', 'tax_plate')->where('status', 'pending')->update(['status' => 'approved']);
        return back()->with('success', 'Satıcı doğrulandı ve aktif edildi.');
    }

    public function rejectVerification(Vendor $vendor)
    {
        $vendor->update([
            'verification_status' => 'rejected',
            'is_active' => false,
        ]);
        $vendor->documents()->where('document_type', 'tax_plate')->where('status', 'pending')->update(['status' => 'rejected']);
        return back()->with('success', 'Satıcı reddedildi (pasif).');
    }

    public function giftBalance(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
        $amount = round((float) $validated['amount'], 2);
        $note = $validated['description'] ?: 'Yönetici hediye bakiyesi';

        DB::transaction(function () use ($vendor, $amount, $note) {
            $locked = Vendor::query()->whereKey($vendor->id)->lockForUpdate()->firstOrFail();
            $locked->increment('balance', $amount);
            $locked->refresh();
            $locked->balanceTransactions()->create([
                'amount' => $amount,
                'type' => 'gift',
                'reference_type' => 'admin',
                'reference_id' => auth()->id(),
                'description' => $note,
                'balance_after' => $locked->balance,
            ]);
        });

        if ($vendor->user) {
            app(NotificationService::class)->notify(
                $vendor->user,
                'Hediye bakiye',
                'Hesabınıza ₺'.number_format($amount, 2, ',', '.').' hediye bakiye yüklendi.',
                ['type' => 'gift_balance'],
                route('vendor.balance.index')
            );
        }

        return back()->with('success', 'Hediye bakiye yüklendi.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->user) {
            $vendor->user->update(['vendor_id' => null, 'role' => 'customer']);
        }
        $vendor->delete();
        return redirect()->route('admin.vendors.index')->with('success', 'Satıcı silindi.');
    }
}
