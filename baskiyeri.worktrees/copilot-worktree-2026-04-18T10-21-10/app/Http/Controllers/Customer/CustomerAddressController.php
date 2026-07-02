<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->latest()->get();

        return view('customer.addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('customer.addresses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'is_default' => ['boolean'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['label'] = $validated['label'] ?? 'Adres';
        if (! empty($validated['is_default'])) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        Address::create($validated);

        return redirect()->route('customer.addresses.index')->with('success', 'Adres kaydedildi.');
    }

    public function edit(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);

        return view('customer.addresses.edit', compact('address'));
    }

    public function update(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'is_default' => ['boolean'],
        ]);
        if (! empty($validated['is_default'])) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }
        $address->update($validated);

        return redirect()->route('customer.addresses.index')->with('success', 'Adres güncellendi.');
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $address->delete();

        return redirect()->route('customer.addresses.index')->with('success', 'Adres silindi.');
    }

    private function authorizeAddress(Request $request, Address $address): void
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
