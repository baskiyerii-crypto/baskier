<?php

namespace App\Services;

use App\Models\OohInventory;
use App\Models\OohOccupancy;
use App\Models\OohRepresentation;
use App\Models\OohVendorRequest;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\VendorMember;
use Illuminate\Support\Facades\Schema;

class OutdoorDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function metrics(Vendor $vendor): array
    {
        $monthStart = now()->startOfMonth();
        $isAgency = $vendor->isOutdoorAgency();
        $pendingRequests = 0;
        $acceptedMonth = 0;
        $monthRevenue = 0.0;
        $inventoryCount = 0;
        $publishedCount = 0;
        $occupancyBooked = 0;
        $occupancyDays = 0;
        $representationCount = 0;
        $agencyRows = [];
        $staffRows = [];

        if (Schema::hasTable('ooh_vendor_requests')) {
            $pendingRequests = OohVendorRequest::query()
                ->where('vendor_id', $vendor->id)
                ->whereIn('status', [OohVendorRequest::STATUS_PENDING, OohVendorRequest::STATUS_QUOTED])
                ->count();
            $acceptedMonth = OohVendorRequest::query()
                ->where('vendor_id', $vendor->id)
                ->where('status', OohVendorRequest::STATUS_ACCEPTED)
                ->where('updated_at', '>=', $monthStart)
                ->count();
        }

        $monthRevenue = (float) Order::query()
            ->where('vendor_id', $vendor->id)
            ->whereNotNull('ooh_vendor_request_id')
            ->where('created_at', '>=', $monthStart)
            ->whereNotIn('status', ['cancelled', 'pending', 'pending_payment'])
            ->sum('vendor_amount');

        if (! $isAgency && Schema::hasTable('ooh_inventories')) {
            $inventoryCount = $vendor->oohInventories()->count();
            $publishedCount = $vendor->oohInventories()->where('status', OohInventory::STATUS_PUBLISHED)->count();
        } elseif ($isAgency && Schema::hasTable('ooh_inventories')) {
            $ownerIds = app(OutdoorRepresentationService::class)->representedOwnerIds($vendor);
            $inventoryCount = OohInventory::query()->whereIn('vendor_id', $ownerIds ?: [0])->count();
            $publishedCount = OohInventory::query()
                ->whereIn('vendor_id', $ownerIds ?: [0])
                ->where('status', OohInventory::STATUS_PUBLISHED)
                ->count();
        }

        if (Schema::hasTable('ooh_occupancies') && Schema::hasTable('ooh_inventories')) {
            $occQuery = OohOccupancy::query()
                ->where('kind', OohOccupancy::KIND_BOOKED)
                ->whereDate('ends_on', '>=', $monthStart->toDateString())
                ->whereHas('inventory', function ($q) use ($vendor, $isAgency) {
                    if ($isAgency) {
                        $ids = app(OutdoorRepresentationService::class)->representedOwnerIds($vendor);
                        $q->whereIn('vendor_id', $ids ?: [0]);
                    } else {
                        $q->where('vendor_id', $vendor->id);
                    }
                });
            $occupancyBooked = (clone $occQuery)->count();
            $occupancyDays = (int) (clone $occQuery)->get()->sum(function (OohOccupancy $row) {
                return $row->starts_on && $row->ends_on
                    ? $row->starts_on->diffInDays($row->ends_on) + 1
                    : 0;
            });
        }

        if (Schema::hasTable('ooh_representations')) {
            $representationCount = OohRepresentation::query()
                ->where(function ($q) use ($vendor) {
                    $q->where('owner_vendor_id', $vendor->id)->orWhere('agency_vendor_id', $vendor->id);
                })
                ->where('status', OohRepresentation::STATUS_ACTIVE)
                ->count();

            if (! $isAgency) {
                $binds = OohRepresentation::query()
                    ->with('agency')
                    ->where('owner_vendor_id', $vendor->id)
                    ->where('status', OohRepresentation::STATUS_ACTIVE)
                    ->get();
                foreach ($binds as $bind) {
                    $agencyId = (int) $bind->agency_vendor_id;
                    $agencyRows[] = [
                        'name' => $bind->agency?->name ?? '#'.$agencyId,
                        'exclusive' => (bool) $bind->exclusive,
                        'revenue' => (float) Order::query()
                            ->where('vendor_id', $agencyId)
                            ->whereNotNull('ooh_vendor_request_id')
                            ->where('created_at', '>=', $monthStart)
                            ->sum('vendor_amount'),
                    ];
                }
            }
        }

        if (! $isAgency && Schema::hasTable('vendor_members') && Schema::hasTable('ooh_occupancies')) {
            $members = VendorMember::query()->with('user')->where('vendor_id', $vendor->id)->get();
            foreach ($members as $member) {
                $assigned = OohOccupancy::query()
                    ->where('assigned_user_id', $member->user_id)
                    ->where('kind', OohOccupancy::KIND_BOOKED)
                    ->count();
                $proved = 0;
                if (Schema::hasTable('ooh_proofs')) {
                    $proved = OohOccupancy::query()
                        ->where('assigned_user_id', $member->user_id)
                        ->whereHas('proofs', fn ($q) => $q->where('is_valid', true))
                        ->count();
                }
                $staffRows[] = [
                    'name' => $member->user?->name ?? 'Üye',
                    'role' => $member->staff_role,
                    'jobs' => $assigned,
                    'proved' => $proved,
                ];
            }
        }

        return [
            'pendingRequests' => $pendingRequests,
            'acceptedMonth' => $acceptedMonth,
            'monthRevenue' => $monthRevenue,
            'inventoryCount' => $inventoryCount,
            'publishedCount' => $publishedCount,
            'occupancyBooked' => $occupancyBooked,
            'occupancyDays' => $occupancyDays,
            'representationCount' => $representationCount,
            'agencyRows' => $agencyRows,
            'staffRows' => $staffRows,
            'isAgency' => $isAgency,
        ];
    }
}
