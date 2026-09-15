<?php

namespace App\Http\Requests\Api\V1;

use App\Models\BusinessType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:customer,vendor'],
            'device_name' => ['required', 'string', 'max:255'],
            'business_type_ids' => ['nullable', 'array'],
            'business_type_ids.*' => ['exists:business_types,id'],
        ];

        if ($this->input('role') === 'vendor' && BusinessType::query()->exists()) {
            $rules['business_type_ids'] = ['required', 'array', 'min:1'];
        }

        return $rules;
    }
}
