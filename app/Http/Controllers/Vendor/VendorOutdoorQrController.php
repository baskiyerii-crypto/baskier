<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OohInventory;
use App\Services\OutdoorQrService;
use App\Services\OutdoorStaffService;
use Illuminate\Http\Request;

class VendorOutdoorQrController extends Controller
{
    public function __construct(
        private OutdoorStaffService $staff,
        private OutdoorQrService $qr,
    ) {}

    public function show(Request $request, OohInventory $inventory)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor && (int) $inventory->vendor_id === (int) $vendor->id, 403);
        $this->staff->assertCanEditInventory($request->user(), $inventory);

        return response($this->qr->svg($inventory), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store',
        ]);
    }
}
