<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class OohQuoteSelectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'share_my_contact' => ['accepted'],
            'accept_vendor_contact' => ['accepted'],
            'accept_consent' => ['accepted'],
            'accept_consent_scrolled_at' => ['required', 'date'],
        ];
    }
}
