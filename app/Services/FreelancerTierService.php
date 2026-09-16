<?php

namespace App\Services;

use App\Models\Vendor;

class FreelancerTierService
{
    public function calculate(Vendor $vendor): ?string
    {
        $count = $vendor->documents()
            ->where('status', 'approved')
            ->whereIn('document_type', ['certificate', 'diploma', 'course', 'other', 'tax_plate'])
            ->count();

        if ($count <= 0) {
            return null;
        }
        if ($count === 1) {
            return 'standard';
        }
        if ($count <= 3) {
            return 'medium';
        }

        return 'professional';
    }

    public function apply(Vendor $vendor, ?string $override = null): Vendor
    {
        $tier = $override ?: $this->calculate($vendor);
        $vendor->update(['freelancer_tier' => $tier]);

        return $vendor->fresh();
    }
}
