<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OohPlan;
use App\Models\OohVendorRequest;
use App\Services\OutdoorPlanBasket;
use App\Services\OutdoorPlanService;
use App\Services\OutdoorStaffService;
use Illuminate\Http\Request;
use RuntimeException;

class VendorOutdoorRequestController extends Controller
{
    public function __construct(
        private OutdoorStaffService $staff,
        private OutdoorPlanService $plans,
    ) {}

    private function vendor(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor || ! $vendor->hasActiveOutdoorModule()) {
            abort(403, 'Açık hava modülü aktif değil.');
        }
        $this->staff->ensureOwner($vendor);

        return $vendor;
    }

    public function index(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->staff->assertCanOperate($request->user(), $vendor);
        $requests = OohVendorRequest::query()
            ->with(['plan.planner', 'latestQuote', 'plan.items.inventory'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(20);

        return view('vendor.outdoor.requests-index', compact('vendor', 'requests'));
    }

    public function show(Request $request, OohVendorRequest $vendorRequest)
    {
        $vendor = $this->vendor($request);
        abort_unless((int) $vendorRequest->vendor_id === (int) $vendor->id, 403);
        $this->authorize('view', $vendorRequest);
        $this->staff->assertCanOperate($request->user(), $vendor);
        $vendorRequest->load(['plan.items.inventory.images', 'plan.planner', 'quotes']);

        return view('vendor.outdoor.request-show', compact('vendor', 'vendorRequest'));
    }

    public function quote(Request $request, OohVendorRequest $vendorRequest)
    {
        $vendor = $this->vendor($request);
        abort_unless((int) $vendorRequest->vendor_id === (int) $vendor->id, 403);
        $this->authorize('quote', $vendorRequest);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:2000'],
            'share_my_contact' => ['accepted'],
        ]);
        try {
            $this->plans->quote(
                $vendorRequest,
                $request->user(),
                (float) $validated['amount'],
                $validated['note'] ?? null,
                true
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Teklif gönderildi.');
    }

    public function decline(Request $request, OohVendorRequest $vendorRequest)
    {
        $vendor = $this->vendor($request);
        abort_unless((int) $vendorRequest->vendor_id === (int) $vendor->id, 403);
        $this->authorize('decline', $vendorRequest);
        try {
            $this->plans->decline($vendorRequest, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Talep reddedildi, hold kaldırıldı.');
    }

    public function myPlans(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->staff->assertCanOperate($request->user(), $vendor);
        $plans = OohPlan::query()
            ->with(['vendorRequests.latestQuote', 'items.inventory'])
            ->where('planner_vendor_id', $vendor->id)
            ->latest()
            ->paginate(20);

        return view('vendor.outdoor.plans-index', compact('vendor', 'plans'));
    }

    public function showPlan(Request $request, OohPlan $plan)
    {
        $vendor = $this->vendor($request);
        abort_unless((int) $plan->planner_vendor_id === (int) $vendor->id, 403);
        $plan->load(['items.inventory', 'vendorRequests.vendor', 'vendorRequests.latestQuote']);

        return view('vendor.outdoor.plan-show', compact('vendor', 'plan'));
    }

    public function storePlan(Request $request, OutdoorPlanBasket $basket)
    {
        $vendor = $this->vendor($request);
        $this->staff->assertCanOperate($request->user(), $vendor);
        $this->authorize('create', OohPlan::class);
        $lines = $basket->lines($request);
        if ($lines === []) {
            return back()->with('error', 'Plan sepeti boş. Katalogdan pano ekleyin.');
        }
        try {
            $plan = $this->plans->submit(
                $request->user(),
                $lines,
                OohPlan::PLANNER_VENDOR,
                $vendor,
                $request->input('title'),
                $request->input('note')
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        $basket->clear($request);

        return redirect()->route('vendor.outdoor.plans.show', $plan)->with('success', 'Havuz planı ilgili sahiplere iletildi.');
    }
}
