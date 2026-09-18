<?php

namespace App\Http\Controllers\OutdoorPanel;

use App\Http\Controllers\Controller;
use App\Models\OohRepresentation;
use App\Models\OohVendorRequest;
use App\Support\OutdoorSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class OutdoorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor && $vendor->hasOutdoorTrack(), 403, 'Açık hava hesabı değil.');

        $pendingRequests = 0;
        $inventoryCount = 0;
        $representationCount = 0;

        try {
            if (Schema::hasTable('ooh_vendor_requests')) {
                $pendingRequests = OohVendorRequest::query()
                    ->where('vendor_id', $vendor->id)
                    ->whereIn('status', [OohVendorRequest::STATUS_PENDING, OohVendorRequest::STATUS_QUOTED])
                    ->count();
            }
        } catch (\Throwable) {
        }

        try {
            if (! $vendor->isOutdoorAgency() && OutdoorSchema::inventoriesReady()) {
                $inventoryCount = $vendor->oohInventories()->count();
            }
        } catch (\Throwable) {
        }

        try {
            if (Schema::hasTable('ooh_representations')) {
                $representationCount = OohRepresentation::query()
                    ->where(function ($q) use ($vendor) {
                        $q->where('owner_vendor_id', $vendor->id)->orWhere('agency_vendor_id', $vendor->id);
                    })
                    ->where('status', OohRepresentation::STATUS_ACTIVE)
                    ->count();
            }
        } catch (\Throwable) {
        }

        return view('outdoor-panel.dashboard', compact(
            'vendor',
            'pendingRequests',
            'inventoryCount',
            'representationCount'
        ));
    }
}
