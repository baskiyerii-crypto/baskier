<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OohInventory;
use App\Models\OohInventoryClaim;
use App\Models\OohOccupancy;
use App\Models\VendorMember;
use App\Models\OohInventoryGrant;
use App\Services\OutdoorClaimService;
use App\Services\OutdoorProofService;
use App\Services\OutdoorStaffService;
use App\Support\OutdoorSchema;
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

    private function assertOwner($vendor): void
    {
        if ($vendor->isOutdoorAgency()) {
            abort(403, 'Asım, ekip ve çift ilan raporları mecra sahibine aittir.');
        }
    }

    public function staffIndex(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->assertOwner($vendor);
        $this->staff->assertAccountOwner($request->user(), $vendor);
        $memberWith = ['user'];
        if (OutdoorSchema::crewsReady()) {
            $memberWith[] = 'crew';
        }
        $members = $vendor->members()->with($memberWith)->get();
        $crews = OutdoorSchema::crewsReady()
            ? $vendor->outdoorCrews()->orderBy('name')->get()
            : collect();
        $grants = OutdoorSchema::inventoryGrantsReady()
            ? OohInventoryGrant::query()
                ->with(['crew', 'user'])
                ->where('vendor_id', $vendor->id)
                ->latest()
                ->get()
            : collect();

        return view('vendor.outdoor.staff', compact('vendor', 'members', 'crews', 'grants'));
    }

    public function storeCrew(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->assertOwner($vendor);
        if (! OutdoorSchema::crewsReady()) {
            return back()->with('error', 'Ekip tabloları henüz kurulmadı.');
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);
        try {
            $this->staff->createCrew($vendor, $request->user(), $validated['name']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Ekip oluşturuldu.');
    }

    public function staffInvite(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->assertOwner($vendor);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8'],
            'crew_id' => ['nullable', 'integer'],
        ]);
        try {
            $this->staff->invite(
                $vendor,
                $request->user(),
                $validated['phone'],
                $validated['name'],
                \App\Models\VendorMember::ROLE_FIELD,
                $validated['password'],
                ! empty($validated['crew_id']) ? (int) $validated['crew_id'] : null
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Ekip üyesi eklendi.');
    }

    public function storeGrant(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->assertOwner($vendor);
        if (! OutdoorSchema::inventoryGrantsReady() || ! OutdoorSchema::crewsReady()) {
            return back()->with('error', 'Yetki tabloları henüz kurulmadı.');
        }
        $validated = $request->validate([
            'target' => ['required', 'in:crew,user'],
            'crew_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
        try {
            $this->staff->grantInventory(
                $vendor,
                $request->user(),
                $validated['target'] === 'crew' && ! empty($validated['crew_id']) ? (int) $validated['crew_id'] : null,
                $validated['target'] === 'user' && ! empty($validated['user_id']) ? (int) $validated['user_id'] : null,
                \Carbon\Carbon::parse($validated['starts_at']),
                ! empty($validated['ends_at']) ? \Carbon\Carbon::parse($validated['ends_at']) : null
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pano ekleme yetkisi verildi.');
    }

    public function revokeGrant(Request $request, \App\Models\OohInventoryGrant $grant)
    {
        $vendor = $this->vendor($request);
        $this->assertOwner($vendor);
        try {
            $this->staff->revokeGrant($vendor, $request->user(), $grant);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Yetki kaldırıldı.');
    }

    public function jobs(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->assertOwner($vendor);
        $user = $request->user();
        $role = $this->staff->roleFor($user, $vendor);
        $query = OohOccupancy::query()
            ->with(['inventory', 'proofs', 'assignedUser'])
            ->where('kind', OohOccupancy::KIND_BOOKED)
            ->whereHas('inventory', fn ($q) => $q->where('vendor_id', $vendor->id));
        if (! $this->staff->isAccountOwner($user, $vendor)) {
            $query->where('assigned_user_id', $user->id);
        }
        $jobs = $query->orderBy('starts_on')->paginate(20);

        return view('vendor.outdoor.jobs', compact('vendor', 'jobs', 'role'));
    }

    public function assign(Request $request, OohOccupancy $occupancy)
    {
        $vendor = $this->vendor($request);
        $this->assertOwner($vendor);
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
        $this->assertOwner($vendor);
        $occupancy->loadMissing('inventory');
        abort_unless((int) $occupancy->inventory?->vendor_id === (int) $vendor->id, 403);
        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:8192'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'qr_token' => ['required', 'string', 'max:16'],
        ]);
        try {
            $proof = $this->proofs->submit(
                $occupancy,
                $request->user(),
                $request->file('photo'),
                (float) $validated['lat'],
                (float) $validated['lng'],
                $validated['qr_token']
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            $proof->is_valid ? 'success' : 'error',
            $proof->is_valid
                ? 'Asım kanıtı kaydedildi.'
                : 'Kanıt kaydedildi ancak GPS veya QR doğrulanamadı ('.$proof->distance_m.' m). Takvim değişmedi.'
        );
    }

    public function claims(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->assertOwner($vendor);
        $this->staff->assertAccountOwner($request->user(), $vendor);
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
        $this->assertOwner($vendor);
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
