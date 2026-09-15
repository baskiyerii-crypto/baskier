<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $addressId = (int) $this->input('address_id');
        if ($addressId <= 0 || ! $this->user()) {
            return false;
        }

        $address = Address::query()->find($addressId);

        return $address !== null
            && $this->user()->can('view', $address);
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ];
    }
}
