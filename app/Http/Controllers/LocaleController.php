<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, SetLocale::SUPPORTED, true)) {
            $locale = 'tr';
        }

        $request->session()->put('locale', $locale);

        $previous = url()->previous();
        $target = ($previous && $previous !== $request->fullUrl())
            ? $previous
            : route('home');

        return redirect()
            ->to($target)
            ->withCookie(cookie(SetLocale::COOKIE, $locale, 60 * 24 * 365));
    }
}
