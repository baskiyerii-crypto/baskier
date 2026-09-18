<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Exports\OutdoorQuoteExport;
use App\Models\Contract;
use App\Models\OohPlan;
use App\Models\OohQuote;
use App\Models\OohVendorRequest;
use App\Models\Setting;
use App\Services\OutdoorPlanBasket;
use App\Services\OutdoorPlanService;
use App\Services\OutdoorStaffService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
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
        \App\Support\OutdoorSchema::abortIfVendorPanelUnavailable();
        try {
            $this->staff->ensureOwner($vendor);
        } catch (RuntimeException $e) {
            abort(403, $e->getMessage());
        }

        return $vendor;
    }

    public function index(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->staff->assertCanOperate($request->user(), $vendor);
        $status = trim((string) $request->query('status', ''));
        $requests = OohVendorRequest::query()
            ->with(['plan.planner', 'latestQuote', 'plan.items.inventory'])
            ->where('vendor_id', $vendor->id)
            ->when(
                in_array($status, [OohVendorRequest::STATUS_PENDING, OohVendorRequest::STATUS_QUOTED, OohVendorRequest::STATUS_ACCEPTED], true),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('vendor.outdoor.requests-index', compact('vendor', 'requests', 'status'));
    }

    public function show(Request $request, OohVendorRequest $vendorRequest)
    {
        $vendor = $this->vendor($request);
        abort_unless((int) $vendorRequest->vendor_id === (int) $vendor->id, 403);
        $this->authorize('view', $vendorRequest);
        $this->staff->assertCanOperate($request->user(), $vendor);
        $vendorRequest->load(['plan.items.inventory.images', 'plan.planner', 'quotes']);
        $quotedItems = $this->plans->itemsCoveredByRequest($vendorRequest);
        $quotingEnabled = Setting::outdoorQuotingEnabled();
        $quoteFee = Setting::outdoorQuoteFee();
        $quoteFeeThreshold = Setting::outdoorQuoteFeeThreshold();

        return view('vendor.outdoor.request-show', compact(
            'vendor',
            'vendorRequest',
            'quotedItems',
            'quotingEnabled',
            'quoteFee',
            'quoteFeeThreshold'
        ));
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
        $this->staff->assertCanOperate($request->user(), $vendor);
        $plan->load(['items.inventory', 'vendorRequests.vendor', 'vendorRequests.latestQuote']);
        $consentContract = Contract::query()
            ->whereIn('key', ['open_consent', 'kvkk', 'privacy'])
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($c) => ['open_consent' => 0, 'kvkk' => 1, 'privacy' => 2][$c->key] ?? 9)
            ->first();

        return view('vendor.outdoor.plan-show', compact('vendor', 'plan', 'consentContract'));
    }

    public function acceptPlan(Request $request, OohPlan $plan, OohVendorRequest $vendorRequest, OohQuote $quote)
    {
        $vendor = $this->vendor($request);
        abort_unless((int) $plan->planner_vendor_id === (int) $vendor->id, 403);
        abort_unless((int) $plan->planner_user_id === (int) $request->user()->id, 403);
        abort_unless((int) $vendorRequest->ooh_plan_id === (int) $plan->id, 404);
        $this->authorize('select', $vendorRequest);
        $request->validate([
            'share_my_contact' => ['accepted'],
            'accept_vendor_contact' => ['accepted'],
            'accept_consent' => ['accepted'],
            'accept_consent_scrolled_at' => ['required', 'date'],
        ]);
        try {
            $order = $this->plans->accept(
                $vendorRequest,
                $quote,
                $request->user(),
                true,
                true
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('outdoor-panel.plans.show', $plan)
            ->with('success', 'Teklif kabul edildi. Sipariş '.$order->order_number);
    }

    public function exportRequests(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->assertCanExport($request, $vendor);
        $status = trim((string) $request->query('status', ''));
        $rows = OohVendorRequest::query()
            ->with(['plan.planner', 'plan.items.inventory', 'latestQuote', 'vendor'])
            ->where('vendor_id', $vendor->id)
            ->when(
                in_array($status, [OohVendorRequest::STATUS_PENDING, OohVendorRequest::STATUS_QUOTED, OohVendorRequest::STATUS_ACCEPTED], true),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->get();

        return Excel::download(
            new OutdoorQuoteExport($this->exportRows($rows, true)),
            'acik-hava-talepler.xlsx'
        );
    }

    public function exportPlans(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->assertCanExport($request, $vendor);
        $planIds = OohPlan::query()->where('planner_vendor_id', $vendor->id)->pluck('id');
        $rows = OohVendorRequest::query()
            ->with(['plan.items.inventory', 'latestQuote', 'vendor'])
            ->whereIn('ooh_plan_id', $planIds)
            ->latest()
            ->get();

        return Excel::download(
            new OutdoorQuoteExport($this->exportRows($rows, false)),
            'acik-hava-planlar.xlsx'
        );
    }

    private function assertCanExport(Request $request, $vendor): void
    {
        $this->staff->assertCanOperate($request->user(), $vendor);
        if ($this->staff->isFieldOperator($request->user(), $vendor)) {
            abort(403, 'Saha personeli Excel indiremez.');
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, OohVendorRequest>  $requests
     * @return list<array<int, string|int|float|null>>
     */
    private function exportRows($requests, bool $incoming): array
    {
        $out = [];
        foreach ($requests as $req) {
            $items = $this->plans->itemsCoveredByRequest($req);
            if ($items->isEmpty()) {
                $items = collect([null]);
            }
            $counterparty = $incoming
                ? ($req->plan?->planner?->name ?: $req->plan?->title)
                : $req->vendor?->name;
            foreach ($items as $item) {
                $inv = $item?->inventory;
                $lat = $inv?->lat;
                $lng = $inv?->lng;
                $map = ($lat && $lng)
                    ? 'https://www.openstreetmap.org/?mlat='.$lat.'&mlon='.$lng.'#map=17/'.$lat.'/'.$lng
                    : '';
                $out[] = [
                    $req->plan?->title,
                    $counterparty,
                    $req->status,
                    $req->latestQuote?->amount,
                    $item?->starts_on?->toDateString(),
                    $item?->ends_on?->toDateString(),
                    $inv?->title,
                    $inv?->permit_no,
                    $inv?->city,
                    $inv?->district,
                    $lat,
                    $lng,
                    $map,
                ];
            }
        }

        return $out;
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

        return redirect()->route('outdoor-panel.plans.show', $plan)->with('success', 'Plan talebi ilgili satıcılara iletildi.');
    }
}
