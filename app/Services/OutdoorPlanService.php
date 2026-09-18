<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Domain\PaymentStatus;
use App\Models\OohInventory;
use App\Models\OohPlan;
use App\Models\OohPlanItem;
use App\Models\OohQuote;
use App\Models\OohVendorRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OutdoorPlanService
{
    public function __construct(
        private OutdoorOccupancyService $occupancy,
        private OutdoorStaffService $staff,
        private ContactShareService $shares,
        private CommissionService $commissions,
        private NotificationService $notifications,
        private OutdoorRepresentationService $representations,
    ) {}

    /**
     * @param  list<array{inventory_id:int, starts_on:string, ends_on:string}>  $lines
     */
    public function submit(User $planner, array $lines, string $plannerType = OohPlan::PLANNER_CUSTOMER, ?Vendor $plannerVendor = null, ?string $title = null, ?string $note = null): OohPlan
    {
        if ($lines === []) {
            throw new RuntimeException('En az bir pano seçin.');
        }

        return DB::transaction(function () use ($planner, $lines, $plannerType, $plannerVendor, $title, $note) {
            $plan = OohPlan::create([
                'planner_type' => $plannerType,
                'planner_user_id' => $planner->id,
                'planner_vendor_id' => $plannerVendor?->id,
                'title' => $title ?: 'Açık hava planı',
                'note' => $note,
                'status' => OohPlan::STATUS_PENDING_QUOTES,
            ]);

            $byVendor = [];
            foreach ($lines as $line) {
                $inventory = OohInventory::query()
                    ->whereKey($line['inventory_id'])
                    ->where('status', OohInventory::STATUS_PUBLISHED)
                    ->first();
                if (! $inventory) {
                    throw new RuntimeException('Yayınlanmamış veya bulunamayan pano seçildi.');
                }
                $start = Carbon::parse($line['starts_on'])->startOfDay();
                $end = Carbon::parse($line['ends_on'])->startOfDay();
                if ($end->lt($start)) {
                    throw new RuntimeException('Bitiş tarihi başlangıçtan önce olamaz.');
                }
                $this->occupancy->assertAvailable($inventory, $start, $end);

                $item = OohPlanItem::create([
                    'ooh_plan_id' => $plan->id,
                    'ooh_inventory_id' => $inventory->id,
                    'owner_vendor_id' => $inventory->vendor_id,
                    'starts_on' => $start->toDateString(),
                    'ends_on' => $end->toDateString(),
                    'list_price_snapshot' => $inventory->list_price,
                ]);
                $this->occupancy->placeHold($inventory, $start, $end, $item->id);
                foreach ($this->representations->sellerVendorIdsForInventory($inventory) as $sellerId) {
                    $byVendor[$sellerId][] = $item;
                }
            }

            foreach ($byVendor as $vendorId => $items) {
                $request = OohVendorRequest::create([
                    'ooh_plan_id' => $plan->id,
                    'vendor_id' => $vendorId,
                    'status' => OohVendorRequest::STATUS_PENDING,
                ]);
                $vendor = Vendor::query()->find($vendorId);
                if ($vendor?->user) {
                    $this->notifications->notify(
                        $vendor->user,
                        'Yeni açık hava plan talebi',
                        $plan->title.' · '.count($items).' pano',
                        ['type' => 'ooh_plan', 'plan_id' => $plan->id],
                        route('outdoor-panel.requests.show', $request)
                    );
                }
            }

            return $plan->load(['items.inventory', 'vendorRequests.vendor']);
        });
    }

    public function quote(OohVendorRequest $request, User $actor, float $amount, ?string $note, bool $vendorConsented): OohQuote
    {
        $this->staff->assertCanOperate($actor, $request->vendor);
        if (! in_array($request->status, [OohVendorRequest::STATUS_PENDING, OohVendorRequest::STATUS_QUOTED], true)) {
            throw new RuntimeException('Bu talebe teklif verilemez.');
        }
        if ($amount <= 0) {
            throw new RuntimeException('Teklif tutarı geçersiz.');
        }

        return DB::transaction(function () use ($request, $amount, $note, $vendorConsented) {
            $quote = OohQuote::create([
                'ooh_vendor_request_id' => $request->id,
                'vendor_id' => $request->vendor_id,
                'amount' => $amount,
                'note' => $note,
                'vendor_consented' => $vendorConsented,
                'status' => OohQuote::STATUS_PENDING,
            ]);
            $request->update([
                'status' => OohVendorRequest::STATUS_QUOTED,
                'vendor_consented' => $vendorConsented,
                'quoted_at' => now(),
            ]);

            $planner = $request->plan?->planner;
            if ($planner) {
                $this->notifications->notify(
                    $planner,
                    'Açık hava teklifi geldi',
                    $request->vendor?->name.' · ₺'.number_format($amount, 2, ',', '.'),
                    ['type' => 'ooh_quote', 'plan_id' => $request->ooh_plan_id],
                    $this->plannerPlanUrl($request->plan)
                );
            }

            return $quote;
        });
    }

    public function decline(OohVendorRequest $request, User $actor): void
    {
        $this->staff->assertCanOperate($actor, $request->vendor);
        if (! in_array($request->status, [OohVendorRequest::STATUS_PENDING, OohVendorRequest::STATUS_QUOTED], true)) {
            throw new RuntimeException('Bu talep reddedilemez.');
        }

        $request->loadMissing(['plan.planner', 'vendor']);

        DB::transaction(function () use ($request) {
            $request->update([
                'status' => OohVendorRequest::STATUS_DECLINED,
                'resolved_at' => now(),
            ]);
            foreach ($this->itemIdsCoveredByRequest($request) as $itemId) {
                if ($this->itemStillHasOpenRequest($request->plan, $itemId, (int) $request->id)) {
                    continue;
                }
                $this->occupancy->releasePlanItem($itemId);
            }
            $this->refreshPlanStatus($request->plan);
        });

        $planner = $request->plan?->planner;
        if ($planner) {
            $plan = $request->plan;
            $url = $this->plannerPlanUrl($plan);
            $this->notifications->notify(
                $planner,
                'Açık hava talebi reddedildi',
                ($request->vendor?->name ?? 'Satıcı').' talebi reddetti. Hold kaldırıldı.',
                ['type' => 'ooh_declined', 'ooh_vendor_request_id' => $request->id],
                $url
            );
        }
    }

    public function accept(OohVendorRequest $request, OohQuote $quote, User $customer, bool $shareMine, bool $acceptVendorContact): Order
    {
        if ((int) $request->plan->planner_user_id !== (int) $customer->id) {
            abort(403);
        }
        if ($quote->ooh_vendor_request_id !== $request->id || $quote->status !== OohQuote::STATUS_PENDING) {
            throw new RuntimeException('Teklif seçilemez.');
        }
        if ($request->status !== OohVendorRequest::STATUS_QUOTED) {
            throw new RuntimeException('Satıcı henüz teklif vermedi.');
        }
        if (! $shareMine || ! $acceptVendorContact) {
            throw new RuntimeException(__('panel.consent_required'));
        }
        if (! $quote->vendor_consented && ! $request->vendor_consented) {
            throw new RuntimeException(__('panel.waiting_vendor_consent'));
        }

        return DB::transaction(function () use ($request, $quote, $customer) {
            $quote->update(['status' => OohQuote::STATUS_SELECTED]);
            $request->quotes()->where('id', '!=', $quote->id)->update(['status' => OohQuote::STATUS_REJECTED]);
            $request->update([
                'status' => OohVendorRequest::STATUS_ACCEPTED,
                'resolved_at' => now(),
            ]);

            $this->shares->shareAfterAccept(
                $customer,
                $request->vendor,
                'ooh_vendor_request',
                $request->id,
                true,
                true,
                false
            );

            $itemIds = $this->itemIdsCoveredByRequest($request);
            foreach ($itemIds as $itemId) {
                $this->occupancy->convertHoldToBooked($itemId);
            }
            $this->rejectCompetingRequests($request, $itemIds);

            [$rate, $commissionAmount, $vendorAmount] = $this->commissions->calculate((float) $quote->amount, 'outdoor');
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $customer->id,
                'vendor_id' => $request->vendor_id,
                'type' => 'outdoor',
                'ooh_vendor_request_id' => $request->id,
                'status' => OrderStatus::PENDING_PAYMENT,
                'payment_status' => PaymentStatus::PENDING,
                'subtotal' => $quote->amount,
                'commission_rate' => $rate,
                'commission_amount' => $commissionAmount,
                'vendor_amount' => $vendorAmount,
                'paid_at' => null,
                'commission_ready_at' => now()->addDays(Setting::commissionWaitDays()),
            ]);
            $order->items()->create([
                'name' => $request->plan->title.' · '.$request->vendor?->name,
                'price' => $quote->amount,
                'quantity' => 1,
            ]);

            $this->refreshPlanStatus($request->plan->fresh('vendorRequests'));

            if ($request->vendor?->user) {
                $this->notifications->notify(
                    $request->vendor->user,
                    'Açık hava teklifi kabul edildi',
                    '#'.$order->order_number,
                    ['type' => 'ooh_plan', 'order_id' => $order->id],
                    route('vendor.orders.show', $order)
                );
            }
            $this->notifications->notify(
                $customer,
                'Açık hava planı onaylandı',
                '#'.$order->order_number,
                ['type' => 'ooh_plan'],
                route('account.orders.show', $order)
            );

            return $order;
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, OohPlanItem>
     */
    public function itemsCoveredByRequest(OohVendorRequest $request)
    {
        $request->loadMissing(['plan.items.inventory', 'vendor']);
        $seller = $request->vendor;
        $ownerIds = [(int) $request->vendor_id];
        if ($seller && $seller->isOutdoorAgency()) {
            $ownerIds = array_merge($ownerIds, $this->representations->representedOwnerIds($seller));
        }

        $ownerIds = array_values(array_unique(array_map('intval', $ownerIds)));

        return $request->plan->items
            ->filter(fn ($item) => in_array((int) $item->owner_vendor_id, $ownerIds, true))
            ->values();
    }

    /**
     * @return list<int>
     */
    private function itemIdsCoveredByRequest(OohVendorRequest $request): array
    {
        return $this->itemsCoveredByRequest($request)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @param  list<int>  $acceptedItemIds
     */
    private function rejectCompetingRequests(OohVendorRequest $accepted, array $acceptedItemIds): void
    {
        if ($acceptedItemIds === []) {
            return;
        }

        $accepted->loadMissing('plan.vendorRequests.vendor');
        foreach ($accepted->plan->vendorRequests as $other) {
            if ((int) $other->id === (int) $accepted->id) {
                continue;
            }
            if (! in_array($other->status, [OohVendorRequest::STATUS_PENDING, OohVendorRequest::STATUS_QUOTED], true)) {
                continue;
            }
            $otherIds = $this->itemIdsCoveredByRequest($other);
            if (array_intersect($acceptedItemIds, $otherIds) === []) {
                continue;
            }
            $other->quotes()->where('status', OohQuote::STATUS_PENDING)->update(['status' => OohQuote::STATUS_REJECTED]);
            $other->update([
                'status' => OohVendorRequest::STATUS_DECLINED,
                'resolved_at' => now(),
            ]);
        }
    }

    private function itemStillHasOpenRequest(OohPlan $plan, int $itemId, int $exceptRequestId): bool
    {
        $plan->loadMissing('vendorRequests.vendor', 'items');
        foreach ($plan->vendorRequests as $req) {
            if ((int) $req->id === $exceptRequestId) {
                continue;
            }
            if (! in_array($req->status, [OohVendorRequest::STATUS_PENDING, OohVendorRequest::STATUS_QUOTED], true)) {
                continue;
            }
            if (in_array($itemId, $this->itemIdsCoveredByRequest($req), true)) {
                return true;
            }
        }

        return false;
    }

    private function plannerPlanUrl(?OohPlan $plan): ?string
    {
        if (! $plan) {
            return null;
        }

        return $plan->planner_type === OohPlan::PLANNER_VENDOR
            ? route('outdoor-panel.plans.show', $plan)
            : route('customer.outdoor.plans.show', $plan);
    }

    private function refreshPlanStatus(OohPlan $plan): void
    {
        $statuses = $plan->vendorRequests()->pluck('status');
        $open = $statuses->contains(fn ($s) => in_array($s, [OohVendorRequest::STATUS_PENDING, OohVendorRequest::STATUS_QUOTED], true));
        $accepted = $statuses->contains(OohVendorRequest::STATUS_ACCEPTED);

        if (! $open && $accepted) {
            $plan->update(['status' => OohPlan::STATUS_ACCEPTED]);

            return;
        }
        if ($accepted && $open) {
            $plan->update(['status' => OohPlan::STATUS_PARTIAL]);

            return;
        }
        if ($statuses->every(fn ($s) => in_array($s, [OohVendorRequest::STATUS_DECLINED, OohVendorRequest::STATUS_EXPIRED], true))) {
            $plan->update(['status' => OohPlan::STATUS_CANCELLED]);
        }
    }
}
