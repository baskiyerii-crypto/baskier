<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class OohPlanStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required', 'integer', 'exists:ooh_inventories,id'],
            'items.*.starts_on' => ['required', 'date', 'after_or_equal:today'],
            'items.*.ends_on' => ['required', 'date', 'after_or_equal:items.*.starts_on'],
        ];
    }
}
