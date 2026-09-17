<?php

namespace App\Policies;

use App\Models\OohPlan;
use App\Models\User;

class OohPlanPolicy
{
    public function view(User $user, OohPlan $plan): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ((int) $plan->planner_user_id === (int) $user->id) {
            return true;
        }
        if ($user->vendor_id && $plan->vendorRequests()->where('vendor_id', $user->vendor_id)->exists()) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isVendor();
    }
}
