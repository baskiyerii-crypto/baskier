<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends ApiController
{
    public function index(Request $request)
    {
        return $this->ok($request->user()->addresses()->orderByDesc('is_default')->get());
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

        $address = Address::create($validated);
        Address::capturePlaceHint($validated);

        return $this->ok($address, null, null, 201);
    }

    public function update(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $validated = $request->validate(Address::validationRules($request));
        $validated = Address::withTurkiyeLocation($validated);
        if (! empty($validated['is_default'])) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }
        $address->update($validated);
        Address::capturePlaceHint($validated);

        return $this->ok($address);
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $address->delete();

        return $this->ok(['deleted' => true]);
    }

    private function authorizeAddress(Request $request, Address $address): void
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
