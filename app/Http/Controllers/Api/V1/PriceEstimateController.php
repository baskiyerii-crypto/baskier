<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\PricingEstimateService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PriceEstimateController extends ApiController
{
    public function bundle(Request $request, PricingEstimateService $pricing)
    {
        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1', 'max:50'],
            'lines.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->where(fn ($q) => $q->where('is_active', true))],
            'lines.*.quantity' => ['required', 'integer', 'min:1', 'max:99999'],
        ]);

        return $this->ok($pricing->estimateBundle($validated['lines']));
    }
}
