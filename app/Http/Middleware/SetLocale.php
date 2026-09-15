<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const COOKIE = 'locale';

    public const SUPPORTED = ['tr', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie(self::COOKIE)
            ?? $request->session()->get('locale')
            ?? config('app.locale', 'tr');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = 'tr';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
