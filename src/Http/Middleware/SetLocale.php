<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));
        $available = array_keys(config('auth-package.available_locales', []));

        if (in_array($locale, $available, true)) {
            app()->setLocale($locale);
            app('translator')->setLocale($locale);
        }

        return $next($request);
    }
}
