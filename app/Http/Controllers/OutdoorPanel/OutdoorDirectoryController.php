<?php

namespace App\Http\Controllers\OutdoorPanel;

use App\Http\Controllers\Controller;
use App\Models\OohInventory;
use App\Models\OohRepresentation;
use App\Models\Vendor;
use App\Services\OutdoorRepresentationService;
use Illuminate\Http\Request;
use RuntimeException;

class OutdoorDirectoryController extends Controller
{
    public function __construct(private OutdoorRepresentationService $representations) {}

    public function index(Request $request)
    {
        $vendor = $this->vendor($request);
        $seekingAgencies = $vendor->isOutdoorOwner();
        $query = Vendor::query()
            ->whereKeyNot($vendor->id)
            ->where(function ($q) {
                $q->where('registration_tracks', 'like', '%outdoor%')
                    ->orWhereNotNull('outdoor_role');
            });
        if ($seekingAgencies) {
            $query->where('outdoor_role', Vendor::OUTDOOR_ROLE_AGENCY);
        } else {
            $query->where(function ($q) {
                $q->where('outdoor_role', Vendor::OUTDOOR_ROLE_OWNER)
                    ->orWhereNull('outdoor_role');
            });
        }
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($inner) use ($q) {
                $inner->where('name', 'like', '%'.$q.'%')
                    ->orWhere('city', 'like', '%'.$q.'%');
            });
        }
        $profiles = $query->orderBy('name')->paginate(20)->withQueryString();
        $publishedCounts = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('ooh_inventories')) {
            $publishedCounts = OohInventory::query()
                ->where('status', OohInventory::STATUS_PUBLISHED)
                ->whereIn('vendor_id', $profiles->pluck('id'))
                ->selectRaw('vendor_id, COUNT(*) as c')
                ->groupBy('vendor_id')
                ->pluck('c', 'vendor_id');
        }
        $pendingIds = [];
        $activeIds = [];
        if ($this->representations->tablesReady()) {
            $pendingIds = OohRepresentation::query()
                ->where('status', OohRepresentation::STATUS_PENDING)
                ->where(function ($q) use ($vendor) {
                    $q->where('owner_vendor_id', $vendor->id)->orWhere('agency_vendor_id', $vendor->id);
                })
                ->get()
                ->map(fn (OohRepresentation $r) => (int) $r->owner_vendor_id === (int) $vendor->id ? (int) $r->agency_vendor_id : (int) $r->owner_vendor_id)
                ->all();
            $activeIds = OohRepresentation::query()
                ->where('status', OohRepresentation::STATUS_ACTIVE)
                ->where(function ($q) use ($vendor) {
                    $q->where('owner_vendor_id', $vendor->id)->orWhere('agency_vendor_id', $vendor->id);
                })
                ->get()
                ->map(fn (OohRepresentation $r) => (int) $r->owner_vendor_id === (int) $vendor->id ? (int) $r->agency_vendor_id : (int) $r->owner_vendor_id)
                ->all();
        }

        return view('outdoor-panel.directory', compact(
            'vendor',
            'profiles',
            'seekingAgencies',
            'publishedCounts',
            'pendingIds',
            'activeIds'
        ));
    }

    public function connect(Request $request)
    {
        $vendor = $this->vendor($request);
        $validated = $request->validate([
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'exclusive' => ['sometimes', 'boolean'],
        ]);
        try {
            $this->representations->inviteByVendorId(
                $vendor,
                (int) $validated['vendor_id'],
                (bool) ($validated['exclusive'] ?? false)
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Davet gönderildi. Karşı taraf onaylayınca bağ aktif olur.');
    }

    private function vendor(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor || ! $vendor->hasOutdoorTrack()) {
            abort(403, 'Açık hava hesabı değil.');
        }

        return $vendor;
    }
}
