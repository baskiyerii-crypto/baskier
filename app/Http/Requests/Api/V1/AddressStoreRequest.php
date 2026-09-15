<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;

class AddressStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return Address::validationRules($this);
    }
}
