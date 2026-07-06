<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use AhmedAshraf\Auth\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ApiResponse::error(__('kango-auth::auth.account.inactive'), 403);
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('kango.auth.login')
                ->withErrors(['account' => __('kango-auth::auth.account.inactive')]);
        }

        return $next($request);
    }
}
