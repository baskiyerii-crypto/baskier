<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;

class AddressUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $address = $this->route('address');

        return $address instanceof Address
            && $this->user()?->can('update', $address);
    }

    public function rules(): array
    {
        return Address::validationRules($this);
    }
}
