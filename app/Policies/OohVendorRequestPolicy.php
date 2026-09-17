<?php

namespace App\Policies;

use App\Models\OohVendorRequest;
use App\Models\User;
use App\Services\OutdoorStaffService;

class OohVendorRequestPolicy
{
    public function view(User $user, OohVendorRequest $request): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ((int) $request->plan?->planner_user_id === (int) $user->id) {
            return true;
        }
        $vendor = $user->vendor;
        if (! $vendor || (int) $vendor->id !== (int) $request->vendor_id) {
            return false;
        }

        return app(OutdoorStaffService::class)->canOperate($user, $vendor);
    }

    public function quote(User $user, OohVendorRequest $request): bool
    {
        $vendor = $user->vendor;
        if (! $vendor || (int) $vendor->id !== (int) $request->vendor_id) {
            return false;
        }

        return app(OutdoorStaffService::class)->canOperate($user, $vendor);
    }

    public function decline(User $user, OohVendorRequest $request): bool
    {
        return $this->quote($user, $request);
    }

    public function select(User $user, OohVendorRequest $request): bool
    {
        return (int) $request->plan?->planner_user_id === (int) $user->id;
    }
}
