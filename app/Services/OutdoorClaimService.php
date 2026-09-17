<?php

namespace App\Services;

use App\Models\OohInventory;
use App\Models\OohInventoryClaim;
use App\Models\User;
use App\Models\Vendor;
use RuntimeException;

class OutdoorClaimService
{
    public function __construct(private OutdoorStaffService $staff) {}

    public function report(Vendor $reporter, User $actor, OohInventory $inventory, ?string $evidence, ?string $permitNo): OohInventoryClaim
    {
        $this->staff->assertCanManageInventory($actor, $reporter);
        if ((int) $inventory->vendor_id === (int) $reporter->id) {
            throw new RuntimeException('Kendi envanteriniz için bildirim açılamaz.');
        }

        return OohInventoryClaim::create([
            'reporter_vendor_id' => $reporter->id,
            'ooh_inventory_id' => $inventory->id,
            'evidence' => $evidence,
            'permit_no' => $permitNo ?: $inventory->permit_no,
            'status' => OohInventoryClaim::STATUS_PENDING,
        ]);
    }

    public function resolve(OohInventoryClaim $claim, string $status, ?string $adminNote = null): OohInventoryClaim
    {
        if (! in_array($status, [OohInventoryClaim::STATUS_APPROVED, OohInventoryClaim::STATUS_REJECTED], true)) {
            throw new RuntimeException('Geçersiz karar.');
        }
        $claim->update([
            'status' => $status,
            'admin_note' => $adminNote,
            'resolved_at' => now(),
        ]);
        if ($status === OohInventoryClaim::STATUS_APPROVED) {
            $claim->inventory?->update(['status' => OohInventory::STATUS_REJECTED, 'rejection_reason' => 'Çift ilan raporu onaylandı.']);
        }

        return $claim;
    }
}
