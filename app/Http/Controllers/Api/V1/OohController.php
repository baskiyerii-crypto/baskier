<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\OohInventoryStoreRequest;
use App\Http\Requests\Api\V1\OohPlanStoreRequest;
use App\Http\Requests\Api\V1\OohQuoteSelectRequest;
use App\Http\Resources\Api\V1\OohInventoryResource;
use App\Models\OohInventory;
use App\Models\OohPlan;
use App\Models\OohQuote;
use App\Models\OohVendorRequest;
use App\Services\OutdoorInventoryService;
use App\Services\OutdoorPlanService;
use App\Services\OutdoorProofService;
use App\Services\OutdoorStaffService;
use Illuminate\Http\Request;
use RuntimeException;

class OohController extends ApiController
{
    public function inventories(Request $request, OutdoorInventoryService $service)
    {
        $items = $service->publishedCatalog(
            $request->integer('province_id') ?: null,
            $request->integer('district_id') ?: null,
            $request->integer('category_id') ?: null
        );
        $items->setCollection(
            $items->getCollection()->map(fn (OohInventory $inv) => (new OohInventoryResource($inv))->resolve())
        );

        return $this->ok($items);
    }

    public function show(string $id)
    {
        if (! \App\Support\OutdoorSchema::inventoriesReady()) {
            return $this->fail('Kayıt bulunamadı.', null, 404);
        }
        $inventory = OohInventory::query()
            ->with(['images', 'vendor', 'category'])
            ->where(fn ($q) => $q->where('id', $id)->orWhere('slug', $id))
            ->firstOrFail();
        if (! $inventory->isPublished()) {
            $this->authorize('view', $inventory);
        }

        return $this->ok(new OohInventoryResource($inventory));
    }

    public function storeInventory(OohInventoryStoreRequest $request, OutdoorInventoryService $service)
    {
        if (! \App\Support\OutdoorSchema::inventoriesReady()) {
            return $this->fail('Açık hava şeması henüz kurulmadı.', null, 503);
        }
        $vendor = $request->user()->vendor;
        try {
            $inventory = $service->create(
                $vendor,
                $request->user(),
                $request->safe()->except('images'),
                $request->file('images', []) ?: []
            );
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), null, 422);
        }

        $similar = $service->similarListings($inventory);
        $payload = (new OohInventoryResource($inventory->load(['images', 'vendor', 'category'])))->resolve();
        $message = $similar->isNotEmpty()
            ? 'Envanter kaydedildi. Aynı ruhsat/konumda başka ilan var; yönetici karar verir.'
            : 'Envanter kaydedildi.';

        return $this->ok($payload, $message, [
            'similar_inventory_ids' => $similar->pluck('id')->values(),
        ], 201);
    }

    public function storePlan(OohPlanStoreRequest $request, OutdoorPlanService $plans)
    {
        if (! \App\Support\OutdoorSchema::inventoriesReady() || ! \App\Support\OutdoorSchema::plansReady()) {
            return $this->fail('Açık hava şeması henüz kurulmadı.', null, 503);
        }
        $this->authorize('create', OohPlan::class);
        $plannerVendor = null;
        if ($request->user()->isVendor()) {
            $plannerVendor = $request->user()->vendor;
            if (! $plannerVendor?->hasActiveOutdoorModule()) {
                return $this->fail('Açık hava modülü aktif değil.', null, 403);
            }
        }
        $lines = array_map(fn ($row) => [
            'inventory_id' => (int) $row['inventory_id'],
            'starts_on' => $row['starts_on'],
            'ends_on' => $row['ends_on'],
        ], $request->validated()['items']);
        try {
            $plan = $plans->submit(
                $request->user(),
                $lines,
                $plannerVendor ? OohPlan::PLANNER_VENDOR : OohPlan::PLANNER_CUSTOMER,
                $plannerVendor,
                $request->validated()['title'] ?? null,
                $request->validated()['note'] ?? null
            );
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), null, 422);
        }

        return $this->ok($this->planPayload($plan->fresh(['items.inventory', 'vendorRequests.latestQuote'])), 'Plan oluşturuldu.', null, 201);
    }

    public function myPlans(Request $request)
    {
        if (! \App\Support\OutdoorSchema::plansReady()) {
            return $this->ok(\App\Support\OutdoorSchema::emptyPaginator());
        }

        $plans = OohPlan::query()
            ->with(['items.inventory', 'vendorRequests.latestQuote'])
            ->where('planner_user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return $this->ok($plans);
    }

    public function showPlan(Request $request, OohPlan $oohPlan)
    {
        $this->authorize('view', $oohPlan);
        $oohPlan->load(['items.inventory', 'vendorRequests.vendor', 'vendorRequests.quotes', 'vendorRequests.latestQuote']);

        return $this->ok($this->planPayload($oohPlan));
    }

    public function selectQuote(OohQuoteSelectRequest $request, OohPlan $oohPlan, OohVendorRequest $vendorRequest, OohQuote $quote, OutdoorPlanService $plans)
    {
        $this->authorize('select', $vendorRequest);
        abort_unless((int) $vendorRequest->ooh_plan_id === (int) $oohPlan->id, 404);
        try {
            $order = $plans->accept($vendorRequest, $quote, $request->user(), true, true);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), null, 422);
        }

        return $this->ok(['order_id' => $order->id, 'order_number' => $order->order_number], 'Teklif kabul edildi.');
    }

    public function vendorInventories(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor?->hasActiveOutdoorModule()) {
            return $this->fail('Açık hava modülü aktif değil.', null, 403);
        }
        if (! \App\Support\OutdoorSchema::inventoriesReady()) {
            return $this->ok(\App\Support\OutdoorSchema::emptyPaginator());
        }
        $items = $vendor->oohInventories()->with('images')->latest()->paginate(20);

        return $this->ok($items);
    }

    public function vendorRequests(Request $request, OutdoorStaffService $staff)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor?->hasActiveOutdoorModule()) {
            return $this->fail('Açık hava modülü aktif değil.', null, 403);
        }
        if (! \App\Support\OutdoorSchema::plansReady()) {
            return $this->ok(\App\Support\OutdoorSchema::emptyPaginator());
        }
        $staff->assertCanOperate($request->user(), $vendor);
        $rows = OohVendorRequest::query()
            ->with(['plan.items.inventory', 'latestQuote'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(20);

        return $this->ok($rows);
    }

    public function vendorQuote(Request $request, OohVendorRequest $vendorRequest, OutdoorPlanService $plans)
    {
        $this->authorize('quote', $vendorRequest);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:2000'],
            'share_my_contact' => ['accepted'],
        ]);
        try {
            $quote = $plans->quote($vendorRequest, $request->user(), (float) $validated['amount'], $validated['note'] ?? null, true);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), null, 422);
        }

        return $this->ok($quote, 'Teklif gönderildi.', null, 201);
    }

    public function vendorProof(Request $request, OutdoorProofService $proofs)
    {
        $validated = $request->validate([
            'occupancy_id' => ['required', 'exists:ooh_occupancies,id'],
            'photo' => ['required', 'image', 'max:8192'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);
        $row = \App\Models\OohOccupancy::query()->with('inventory')->findOrFail($validated['occupancy_id']);
        try {
            $proof = $proofs->submit($row, $request->user(), $request->file('photo'), (float) $validated['lat'], (float) $validated['lng']);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), null, 422);
        }

        return $this->ok($proof, $proof->is_valid ? 'Kanıt geçerli.' : 'Kanıt yarıçap dışında kaydedildi.', null, 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function planPayload(OohPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'title' => $plan->title,
            'status' => $plan->status,
            'estimated_total' => $plan->estimatedTotal(),
            'quoted_total' => $plan->quotedTotal(),
            'items' => $plan->items->map(fn ($item) => [
                'id' => $item->id,
                'inventory_id' => $item->ooh_inventory_id,
                'title' => $item->inventory?->title,
                'vendor_id' => $item->owner_vendor_id,
                'starts_on' => $item->starts_on?->toDateString(),
                'ends_on' => $item->ends_on?->toDateString(),
            ]),
            'vendor_requests' => $plan->vendorRequests->map(fn ($req) => [
                'id' => $req->id,
                'vendor_id' => $req->vendor_id,
                'vendor_name' => $req->vendor?->name,
                'status' => $req->status,
                'quote_amount' => $req->latestQuote?->amount,
            ]),
        ];
    }
}
