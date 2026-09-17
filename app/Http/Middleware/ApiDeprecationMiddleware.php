<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiDeprecationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Deprecation', '@1798675200');
        $response->headers->set('Sunset', 'Wed, 31 Dec 2026 23:59:59 GMT');
        $response->headers->set('Link', '</api/v1/service-requests>; rel="successor-version"');

        return $response;
    }
}
