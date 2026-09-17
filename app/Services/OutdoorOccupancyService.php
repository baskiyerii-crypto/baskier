<?php

namespace App\Services;

use App\Models\OohInventory;
use App\Models\OohOccupancy;
use App\Models\OohPlan;
use App\Support\OutdoorSchema;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OutdoorOccupancyService
{
    public const HOLD_HOURS = 48;

    public function __construct(
        private NotificationService $notifications,
    ) {}

    public function expireHolds(): int
    {
        if (! OutdoorSchema::occupanciesReady()) {
            return 0;
        }

        $expired = OohOccupancy::query()
            ->with(['inventory', 'planItem.plan.planner'])
            ->where('kind', OohOccupancy::KIND_HOLD)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            return 0;
        }

        $notified = [];
        foreach ($expired as $row) {
            $plan = $row->planItem?->plan;
            $planner = $plan?->planner;
            if (! $planner || isset($notified[$planner->id])) {
                continue;
            }
            $notified[$planner->id] = true;
            $url = $plan && $plan->planner_type === OohPlan::PLANNER_VENDOR
                ? route('vendor.outdoor.plans.show', $plan)
                : ($plan ? route('customer.outdoor.plans.show', $plan) : null);
            $this->notifications->notify(
                $planner,
                'Açık hava hold süresi doldu',
                ($row->inventory?->title ?? 'Pano').' için 48 saatlik rezervasyon düştü.',
                ['type' => 'ooh_hold_expired', 'occupancy_id' => $row->id],
                $url
            );
        }

        return (int) OohOccupancy::query()->whereIn('id', $expired->pluck('id'))->delete();
    }

    public function isAvailable(OohInventory $inventory, CarbonInterface $start, CarbonInterface $end, ?int $ignoreOccupancyId = null): bool
    {
        if (! OutdoorSchema::occupanciesReady()) {
            return true;
        }

        $this->expireHolds();

        return ! $this->overlapQuery($inventory->id, $start, $end, $ignoreOccupancyId)->exists();
    }

    public function assertAvailable(OohInventory $inventory, CarbonInterface $start, CarbonInterface $end, ?int $ignoreOccupancyId = null): void
    {
        if (! $this->isAvailable($inventory, $start, $end, $ignoreOccupancyId)) {
            throw new RuntimeException('Seçilen tarihler bu pano için müsait değil.');
        }
    }

    public function placeHold(OohInventory $inventory, CarbonInterface $start, CarbonInterface $end, ?int $planItemId = null): OohOccupancy
    {
        return DB::transaction(function () use ($inventory, $start, $end, $planItemId) {
            $locked = OohInventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();
            $this->assertAvailable($locked, $start, $end);

            return OohOccupancy::create([
                'ooh_inventory_id' => $locked->id,
                'ooh_plan_item_id' => $planItemId,
                'starts_on' => $start->toDateString(),
                'ends_on' => $end->toDateString(),
                'kind' => OohOccupancy::KIND_HOLD,
                'expires_at' => now()->addHours(self::HOLD_HOURS),
            ]);
        });
    }

    public function convertHoldToBooked(int $planItemId): void
    {
        OohOccupancy::query()
            ->where('ooh_plan_item_id', $planItemId)
            ->where('kind', OohOccupancy::KIND_HOLD)
            ->update([
                'kind' => OohOccupancy::KIND_BOOKED,
                'expires_at' => null,
            ]);
    }

    public function releasePlanItem(int $planItemId): void
    {
        OohOccupancy::query()
            ->where('ooh_plan_item_id', $planItemId)
            ->where('kind', OohOccupancy::KIND_HOLD)
            ->delete();
    }

    public function block(OohInventory $inventory, CarbonInterface $start, CarbonInterface $end): OohOccupancy
    {
        $this->assertAvailable($inventory, $start, $end);

        return OohOccupancy::create([
            'ooh_inventory_id' => $inventory->id,
            'starts_on' => $start->toDateString(),
            'ends_on' => $end->toDateString(),
            'kind' => OohOccupancy::KIND_BLOCKED,
        ]);
    }

    /**
     * @return list<array{starts_on: string, ends_on: string, kind: string}>
     */
    public function calendar(OohInventory $inventory): array
    {
        if (! OutdoorSchema::occupanciesReady()) {
            return [];
        }

        $this->expireHolds();

        return $inventory->occupancies()
            ->where(function ($q) {
                $q->where('kind', '!=', OohOccupancy::KIND_HOLD)
                    ->orWhere(function ($h) {
                        $h->where('kind', OohOccupancy::KIND_HOLD)
                            ->where(function ($e) {
                                $e->whereNull('expires_at')->orWhere('expires_at', '>', now());
                            });
                    });
            })
            ->orderBy('starts_on')
            ->get(['starts_on', 'ends_on', 'kind'])
            ->map(fn (OohOccupancy $row) => [
                'starts_on' => $row->starts_on->toDateString(),
                'ends_on' => $row->ends_on->toDateString(),
                'kind' => $row->kind,
            ])
            ->all();
    }

    private function overlapQuery(int $inventoryId, CarbonInterface $start, CarbonInterface $end, ?int $ignoreOccupancyId)
    {
        $startDate = Carbon::parse($start)->toDateString();
        $endDate = Carbon::parse($end)->toDateString();

        return OohOccupancy::query()
            ->where('ooh_inventory_id', $inventoryId)
            ->when($ignoreOccupancyId, fn ($q) => $q->where('id', '!=', $ignoreOccupancyId))
            ->whereDate('starts_on', '<=', $endDate)
            ->whereDate('ends_on', '>=', $startDate)
            ->where(function ($q) {
                $q->whereIn('kind', [OohOccupancy::KIND_BOOKED, OohOccupancy::KIND_BLOCKED])
                    ->orWhere(function ($h) {
                        $h->where('kind', OohOccupancy::KIND_HOLD)
                            ->where(function ($e) {
                                $e->whereNull('expires_at')->orWhere('expires_at', '>', now());
                            });
                    });
            });
    }
}
