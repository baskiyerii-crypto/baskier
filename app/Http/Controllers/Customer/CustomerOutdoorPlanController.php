<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\OohPlan;
use App\Models\OohQuote;
use App\Models\OohVendorRequest;
use App\Services\OutdoorPlanBasket;
use App\Services\OutdoorPlanService;
use Illuminate\Http\Request;
use RuntimeException;

class CustomerOutdoorPlanController extends Controller
{
    public function index(Request $request)
    {
        if (! \App\Support\OutdoorSchema::plansReady()) {
            $plans = \App\Support\OutdoorSchema::emptyPaginator();

            return view('customer.outdoor.plans-index', compact('plans'));
        }

        $plans = OohPlan::query()
            ->with(['vendorRequests.latestQuote', 'items.inventory'])
            ->where('planner_user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('customer.outdoor.plans-index', compact('plans'));
    }

    public function show(Request $request, OohPlan $plan)
    {
        $this->authorize('view', $plan);
        abort_unless((int) $plan->planner_user_id === (int) $request->user()->id, 403);
        $plan->load([
            'items.inventory.images',
            'items.ownerVendor',
            'vendorRequests.vendor',
            'vendorRequests.quotes',
            'vendorRequests.latestQuote',
        ]);
        $consentContract = \App\Models\Contract::query()
            ->whereIn('key', ['open_consent', 'kvkk', 'privacy'])
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($c) => ['open_consent' => 0, 'kvkk' => 1, 'privacy' => 2][$c->key] ?? 9)
            ->first();
        $contactShares = \App\Models\ContactShare::query()
            ->where('customer_user_id', $request->user()->id)
            ->where('context_type', 'ooh_vendor_request')
            ->whereIn('context_id', $plan->vendorRequests->pluck('id'))
            ->get()
            ->keyBy('context_id');

        return view('customer.outdoor.plan-show', compact('plan', 'consentContract', 'contactShares'));
    }

    public function store(Request $request, OutdoorPlanBasket $basket, OutdoorPlanService $plans)
    {
        $this->authorize('create', OohPlan::class);
        $lines = $basket->lines($request);
        if ($lines === []) {
            return back()->with('error', 'Plan sepeti boş.');
        }
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);
        try {
            $plan = $plans->submit(
                $request->user(),
                $lines,
                OohPlan::PLANNER_CUSTOMER,
                null,
                $validated['title'] ?? null,
                $validated['note'] ?? null
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        $basket->clear($request);

        return redirect()->route('customer.outdoor.plans.show', $plan)->with('success', 'Plan talebi satıcılara iletildi.');
    }

    public function accept(Request $request, OohPlan $plan, OohVendorRequest $vendorRequest, OohQuote $quote, OutdoorPlanService $plans)
    {
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
            $order = $plans->accept(
                $vendorRequest,
                $quote,
                $request->user(),
                true,
                true
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('account.orders.show', $order)->with('success', 'Teklif kabul edildi. İletişim bilgileri paylaşıldı.');
    }
}
