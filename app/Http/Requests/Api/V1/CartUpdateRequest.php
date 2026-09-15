<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CartUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cartItem = $this->route('cartItem');

        return $this->user() !== null
            && $cartItem
            && (int) $cartItem->user_id === (int) $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }
}
