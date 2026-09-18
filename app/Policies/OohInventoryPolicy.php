<?php

namespace App\Policies;

use App\Models\OohInventory;
use App\Models\User;
use App\Services\OutdoorStaffService;

class OohInventoryPolicy
{
    public function view(?User $user, OohInventory $inventory): bool
    {
        if ($inventory->isPublished()) {
            return true;
        }
        if (! $user) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }

        return app(OutdoorStaffService::class)->canEditInventory($user, $inventory);
    }

    public function create(User $user): bool
    {
        $vendor = $user->vendor;
        if (! $vendor || ! $vendor->hasActiveOutdoorModule()) {
            return false;
        }

        return app(OutdoorStaffService::class)->canManageInventory($user, $vendor);
    }

    public function update(User $user, OohInventory $inventory): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        $vendor = $user->vendor;
        if (! $vendor || (int) $vendor->id !== (int) $inventory->vendor_id) {
            return false;
        }

        return app(OutdoorStaffService::class)->canEditInventory($user, $inventory);
    }

    public function delete(User $user, OohInventory $inventory): bool
    {
        return $this->update($user, $inventory);
    }
}
