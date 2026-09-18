<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RedirectOutdoorOnlyFromVendorPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $vendor = $request->user()?->vendor;
        if (! $vendor || ! $vendor->prefersOutdoorPanel() || ! Route::has('outdoor-panel.dashboard')) {
            return $next($request);
        }

        if ($request->routeIs('vendor.documents.*') && Route::has('outdoor-panel.documents.index')) {
            return redirect()->route('outdoor-panel.documents.index');
        }
        if ($request->routeIs('vendor.subscriptions.*') && Route::has('outdoor-panel.subscriptions.index')) {
            return redirect()->route('outdoor-panel.subscriptions.index');
        }
        if ($request->routeIs('vendor.balance.*') && Route::has('outdoor-panel.balance.index')) {
            return redirect()->route('outdoor-panel.balance.index');
        }
        if ($request->routeIs('vendor.payout-requests.*') && Route::has('outdoor-panel.payout-requests.index')) {
            return redirect()->route('outdoor-panel.payout-requests.index');
        }

        return redirect()->route('outdoor-panel.dashboard');
    }
}
