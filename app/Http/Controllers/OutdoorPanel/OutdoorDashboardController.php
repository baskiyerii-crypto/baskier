<?php

namespace App\Http\Controllers\OutdoorPanel;

use App\Http\Controllers\Controller;
use App\Models\OohRepresentation;
use App\Models\OohVendorRequest;
use Illuminate\Http\Request;

class OutdoorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor && $vendor->hasOutdoorTrack(), 403, 'Açık hava hesabı değil.');

        $pendingRequests = OohVendorRequest::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('status', [OohVendorRequest::STATUS_PENDING, OohVendorRequest::STATUS_QUOTED])
            ->count();
        $inventoryCount = $vendor->isOutdoorAgency()
            ? 0
            : $vendor->oohInventories()->count();
        $representationCount = OohRepresentation::query()
            ->where(function ($q) use ($vendor) {
                $q->where('owner_vendor_id', $vendor->id)->orWhere('agency_vendor_id', $vendor->id);
            })
            ->where('status', OohRepresentation::STATUS_ACTIVE)
            ->count();

        return view('outdoor-panel.dashboard', compact(
            'vendor',
            'pendingRequests',
            'inventoryCount',
            'representationCount'
        ));
    }
}
