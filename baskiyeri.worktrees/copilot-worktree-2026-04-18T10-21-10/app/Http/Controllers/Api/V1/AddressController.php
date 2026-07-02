<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->addresses()->orderByDesc('is_default')->get());
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

        $address = Address::create($validated);

        return response()->json($address, 201);
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

        return response()->json($address);
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $address->delete();

        return response()->json(['message' => 'Silindi.']);
    }

    private function authorizeAddress(Request $request, Address $address): void
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
