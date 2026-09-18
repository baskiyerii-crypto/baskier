<?php

namespace App\Http\Controllers\OutdoorPanel;

use App\Http\Controllers\Controller;
use App\Services\DocumentRequirementService;
use App\Services\OutdoorDashboardService;
use App\Services\PayoutService;
use Illuminate\Http\Request;

class OutdoorDashboardController extends Controller
{
    public function index(Request $request, OutdoorDashboardService $dashboard, DocumentRequirementService $kyc, PayoutService $payouts)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor && $vendor->hasOutdoorTrack(), 403, 'Açık hava hesabı değil.');

        $metrics = $dashboard->metrics($vendor);
        $kycStatus = $kyc->statusFor($vendor);
        $availableBalance = $payouts->availableBalance($vendor);
        $isOutdoorPanel = true;

        return view('outdoor-panel.dashboard', array_merge($metrics, compact(
            'vendor',
            'kycStatus',
            'availableBalance',
            'isOutdoorPanel'
        )));
    }
}
