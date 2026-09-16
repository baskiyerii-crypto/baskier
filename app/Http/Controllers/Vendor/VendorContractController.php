<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ContractVendorAcceptance;
use App\Services\ContractPublishService;
use Illuminate\Http\Request;

class VendorContractController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        $pending = ContractVendorAcceptance::query()
            ->with('contract')
            ->where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'expired'])
            ->latest()
            ->get();

        $accepted = ContractVendorAcceptance::query()
            ->with('contract')
            ->where('vendor_id', $vendor->id)
            ->where('status', 'accepted')
            ->latest()
            ->paginate(10);

        return view('vendor.contracts.index', compact('vendor', 'pending', 'accepted'));
    }

    public function accept(Request $request, ContractVendorAcceptance $acceptance, ContractPublishService $publisher)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);
        $publisher->accept($vendor, $acceptance);

        return back()->with('success', __('panel.contract_accepted'));
    }
}
