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
            'billing_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'payment_method' => ['nullable', 'string', 'in:credit_card,bank_transfer,cash_on_delivery'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
            'invoice_type' => ['nullable', 'string', 'in:individual,corporate'],
            'invoice_full_name' => ['nullable', 'string', 'max:255'],
            'invoice_email' => ['nullable', 'email', 'max:190'],
            'invoice_phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
