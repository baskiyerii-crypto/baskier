<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OohInventory;
use App\Models\OohInventoryClaim;
use App\Models\OohPlan;
use App\Services\OutdoorClaimService;
use App\Services\OutdoorInventoryService;
use Illuminate\Http\Request;

class AdminOutdoorController extends Controller
{
    public function inventories(Request $request)
    {
        $status = $request->get('status', 'pending_review');
        $items = OohInventory::query()
            ->with(['vendor', 'category'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $fingerprints = $items->getCollection()->pluck('geo_fingerprint')->filter()->unique();
        $collisionCounts = $fingerprints->isEmpty()
            ? collect()
            : OohInventory::query()
                ->selectRaw('geo_fingerprint, count(*) as c')
                ->whereIn('geo_fingerprint', $fingerprints)
                ->whereIn('status', [OohInventory::STATUS_PUBLISHED, OohInventory::STATUS_PENDING_REVIEW])
                ->groupBy('geo_fingerprint')
                ->pluck('c', 'geo_fingerprint');

        return view('admin.outdoor.inventories', compact('items', 'status', 'collisionCounts'));
    }

    public function publish(OohInventory $inventory, OutdoorInventoryService $service)
    {
        $service->publish($inventory);
        $similar = $service->similarListings($inventory);
        $message = 'Envanter yayınlandı.';
        if ($similar->isNotEmpty()) {
            $message .= ' Uyarı: aynı ruhsat/konumda başka ilan var; çift ilan kuyruğunu kontrol edin.';
        }

        return back()->with('success', $message);
    }

    public function reject(Request $request, OohInventory $inventory, OutdoorInventoryService $service)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);
        $service->reject($inventory, $validated['rejection_reason']);

        return back()->with('success', 'Envanter reddedildi.');
    }

    public function claims(Request $request)
    {
        $claims = OohInventoryClaim::query()
            ->with(['inventory.vendor', 'reporterVendor'])
            ->when($request->get('status', 'pending') !== 'all', fn ($q) => $q->where('status', $request->get('status', 'pending')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.outdoor.claims', compact('claims'));
    }

    public function resolveClaim(Request $request, OohInventoryClaim $claim, OutdoorClaimService $service)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $service->resolve($claim, $validated['status'], $validated['admin_note'] ?? null);

        return back()->with('success', 'Rapor karara bağlandı.');
    }

    public function plans()
    {
        $plans = OohPlan::query()
            ->with(['planner', 'vendorRequests'])
            ->latest()
            ->paginate(20);

        return view('admin.outdoor.plans', compact('plans'));
    }
}
