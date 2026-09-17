<?php

namespace App\Http\Requests\Api\V1;

use App\Models\OohInventory;
use Illuminate\Foundation\Http\FormRequest;

class OohInventoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', OohInventory::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'turkiye_il_id' => ['nullable', 'integer'],
            'turkiye_ilce_id' => ['nullable', 'integer'],
            'city' => ['nullable', 'string', 'max:80'],
            'district' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'permit_no' => ['nullable', 'string', 'max:64'],
            'list_price' => ['nullable', 'numeric', 'min:0'],
            'price_unit' => ['required', 'in:day,week,month'],
            'proof_radius_m' => ['nullable', 'integer', 'min:10', 'max:500'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],
        ];
    }
}
