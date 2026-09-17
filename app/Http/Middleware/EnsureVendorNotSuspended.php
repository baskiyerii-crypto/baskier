<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $vendor = $user?->vendor;
        if (! $vendor) {
            return $next($request);
        }

        if (! $vendor->is_suspended && $vendor->contract_suspended_at === null) {
            return $next($request);
        }

        $writeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        if (in_array($request->method(), $writeMethods, true) && ! $request->routeIs('logout', 'vendor.documents.*', 'vendor.contracts.*', 'vendor.profile.*')) {
            return back()->with('error', $vendor->is_suspended
                ? ('Hesabınız askıya alındı: '.($vendor->suspension_reason ?: 'Yönetim incelemesi devam ediyor.'))
                : 'Sözleşme onayı tamamlanmadan işlem yapılamaz.');
        }

        return $next($request);
    }
}
