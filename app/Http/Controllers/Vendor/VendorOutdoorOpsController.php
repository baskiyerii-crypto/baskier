<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OohInventory;
use App\Models\OohInventoryClaim;
use App\Models\OohOccupancy;
use App\Models\VendorMember;
use App\Services\OutdoorClaimService;
use App\Services\OutdoorProofService;
use App\Services\OutdoorStaffService;
use Illuminate\Http\Request;
use RuntimeException;

class VendorOutdoorOpsController extends Controller
{
    public function __construct(
        private OutdoorStaffService $staff,
        private OutdoorClaimService $claims,
        private OutdoorProofService $proofs,
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

    public function staffIndex(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->staff->assertCanManageInventory($request->user(), $vendor);
        $members = $vendor->members()->with('user')->get();

        return view('vendor.outdoor.staff', compact('vendor', 'members'));
    }

    public function staffInvite(Request $request)
    {
        $vendor = $this->vendor($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'staff_role' => ['required', 'in:ops,field'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        try {
            $this->staff->invite(
                $vendor,
                $request->user(),
                $validated['email'],
                $validated['name'],
                $validated['staff_role'],
                $validated['password'] ?? null
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Ekip üyesi eklendi.');
    }

    public function jobs(Request $request)
    {
        $vendor = $this->vendor($request);
        $user = $request->user();
        $role = $this->staff->roleFor($user, $vendor);
        $query = OohOccupancy::query()
            ->with(['inventory', 'proofs', 'assignedUser'])
            ->where('kind', OohOccupancy::KIND_BOOKED)
            ->whereHas('inventory', fn ($q) => $q->where('vendor_id', $vendor->id));
        if ($role === VendorMember::ROLE_FIELD) {
            $query->where('assigned_user_id', $user->id);
        }
        $jobs = $query->orderBy('starts_on')->paginate(20);

        return view('vendor.outdoor.jobs', compact('vendor', 'jobs', 'role'));
    }

    public function assign(Request $request, OohOccupancy $occupancy)
    {
        $vendor = $this->vendor($request);
        $occupancy->loadMissing('inventory');
        abort_unless((int) $occupancy->inventory?->vendor_id === (int) $vendor->id, 403);
        $this->staff->assertCanOperate($request->user(), $vendor);
        $validated = $request->validate([
            'assigned_user_id' => ['required', 'exists:users,id'],
        ]);
        $member = VendorMember::query()
            ->where('vendor_id', $vendor->id)
            ->where('user_id', $validated['assigned_user_id'])
            ->first();
        if (! $member) {
            return back()->with('error', 'Kullanıcı bu satıcının ekibinde değil.');
        }
        $occupancy->update(['assigned_user_id' => $validated['assigned_user_id']]);

        return back()->with('success', 'Saha personeli atandı.');
    }

    public function proof(Request $request, OohOccupancy $occupancy)
    {
        $vendor = $this->vendor($request);
        $occupancy->loadMissing('inventory');
        abort_unless((int) $occupancy->inventory?->vendor_id === (int) $vendor->id, 403);
        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:8192'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);
        try {
            $proof = $this->proofs->submit(
                $occupancy,
                $request->user(),
                $request->file('photo'),
                (float) $validated['lat'],
                (float) $validated['lng']
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            $proof->is_valid ? 'success' : 'error',
            $proof->is_valid
                ? 'Asım kanıtı kaydedildi.'
                : 'Kanıt kaydedildi ancak GPS yarıçap dışında ('.$proof->distance_m.' m). Takvim değişmedi.'
        );
    }

    public function claims(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->staff->assertCanManageInventory($request->user(), $vendor);
        $claims = OohInventoryClaim::query()
            ->with('inventory')
            ->where('reporter_vendor_id', $vendor->id)
            ->latest()
            ->paginate(20);

        return view('vendor.outdoor.claims', compact('vendor', 'claims'));
    }

    public function storeClaim(Request $request)
    {
        $vendor = $this->vendor($request);
        $validated = $request->validate([
            'ooh_inventory_id' => ['required', 'exists:ooh_inventories,id'],
            'evidence' => ['nullable', 'string', 'max:5000'],
            'permit_no' => ['nullable', 'string', 'max:64'],
        ]);
        $inventory = OohInventory::query()->findOrFail($validated['ooh_inventory_id']);
        try {
            $this->claims->report($vendor, $request->user(), $inventory, $validated['evidence'] ?? null, $validated['permit_no'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Çift ilan raporu iletildi.');
    }
}
