<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\BillingProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerAddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->latest()->get();
        $billingAddresses = $request->user()->addresses()->orderByDesc('is_billing_default')->latest()->get();

        return view('customer.addresses.index', compact('addresses', 'billingAddresses'));
    }

    public function create()
    {
        $savedAddresses = auth()->user()->addresses()->latest()->get();
        $billingProfile = auth()->user()->billingProfiles()->latest('updated_at')->first();

        return view('customer.addresses.create', compact('savedAddresses', 'billingProfile'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(Address::validationRules($request));

        $validated = Address::withTurkiyeLocation($validated);
        $validated['user_id'] = $request->user()->id;
        $validated['label'] = $validated['label'] ?? 'Adres';
        if (! empty($validated['is_default'])) {
            $request->user()->addresses()->update(['is_default' => false]);
        }
        if (! empty($validated['is_billing_default'])) {
            $request->user()->addresses()->update(['is_billing_default' => false]);
        }

        Address::create($validated);
        Address::capturePlaceHint($validated);
        $this->syncBillingProfileFromAddressForm($request);

        return redirect()->route('account.adresler.index')->with('success', 'Adres kaydedildi.');
    }

    public function edit(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $address->load(['turkiyeIlce', 'turkiyeMahalle']);
        $savedAddresses = $request->user()->addresses()->latest()->get();
        $billingProfile = $request->user()->billingProfiles()->latest('updated_at')->first();

        return view('customer.addresses.edit', compact('address', 'savedAddresses', 'billingProfile'));
    }

    public function update(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $validated = $request->validate(Address::validationRules($request));
        $validated = Address::withTurkiyeLocation($validated);
        if (! empty($validated['is_default'])) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }
        if (! empty($validated['is_billing_default'])) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_billing_default' => false]);
        }
        $address->update($validated);
        Address::capturePlaceHint($validated);
        $this->syncBillingProfileFromAddressForm($request);

        return redirect()->route('account.adresler.index')->with('success', 'Adres güncellendi.');
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $address->delete();

        return redirect()->route('account.adresler.index')->with('success', 'Adres silindi.');
    }

    public function setDefault(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return redirect()->route('account.adresler.index', ['tab' => 'teslimat'])->with('success', 'Varsayılan teslimat adresi güncellendi.');
    }

    public function setBillingDefault(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $request->user()->addresses()->update(['is_billing_default' => false]);
        $address->update(['is_billing_default' => true]);

        return redirect()->route('account.adresler.index', ['tab' => 'fatura'])->with('success', 'Varsayılan fatura adresi güncellendi.');
    }

    private function authorizeAddress(Request $request, Address $address): void
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    private function syncBillingProfileFromAddressForm(Request $request): void
    {
        $sameAsAddress = (bool) $request->boolean('billing_same_as_address', true);
        if ($sameAsAddress) {
            return;
        }

        $validated = $request->validate([
            'invoice_type' => ['required', Rule::in(['individual', 'corporate'])],
            'invoice_full_name' => ['required', 'string', 'max:255'],
            'invoice_email' => ['required', 'email', 'max:190'],
            'invoice_phone' => ['required', 'string', 'max:11', 'regex:/^[0-9]{10,11}$/'],
            'invoice_company_name' => ['required_if:invoice_type,corporate', 'nullable', 'string', 'max:255'],
            'invoice_company_address' => ['required_if:invoice_type,corporate', 'nullable', 'string', 'max:255'],
            'invoice_tax_number' => ['required_if:invoice_type,corporate', 'nullable', 'string', 'max:16'],
            'invoice_tax_office' => ['required_if:invoice_type,corporate', 'nullable', 'string', 'max:120'],
            'invoice_identity_number' => ['nullable', 'string', 'max:16'],
        ]);

        BillingProfile::query()->updateOrCreate(
            ['user_id' => $request->user()->id, 'invoice_type' => $validated['invoice_type']],
            [
                'full_name' => $validated['invoice_full_name'],
                'email' => $validated['invoice_email'],
                'phone' => $validated['invoice_phone'],
                'identity_number' => $validated['invoice_type'] === 'individual'
                    ? ($validated['invoice_identity_number'] ?? null)
                    : null,
                'company_name' => $validated['invoice_type'] === 'corporate'
                    ? ($validated['invoice_company_name'] ?? null)
                    : null,
                'company_address' => $validated['invoice_type'] === 'corporate'
                    ? ($validated['invoice_company_address'] ?? null)
                    : null,
                'tax_number' => $validated['invoice_type'] === 'corporate'
                    ? ($validated['invoice_tax_number'] ?? null)
                    : null,
                'tax_office' => $validated['invoice_type'] === 'corporate'
                    ? ($validated['invoice_tax_office'] ?? null)
                    : null,
            ]
        );
    }
}
