<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Oturum gerekli.'], 401);
            }

            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles, true)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Bu işlem için yetkiniz yok.'], 403);
            }

            abort(403, 'Bu sayfaya erişim yetkiniz yok.');
        }

        return $next($request);
    }
}
