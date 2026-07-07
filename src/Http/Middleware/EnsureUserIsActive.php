<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ashtech\LaravelAuthKit\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ApiResponse::error(__('laravel-auth-kit::auth.account.inactive'), 403);
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('auth-kit.login')
                ->withErrors(['account' => __('laravel-auth-kit::auth.account.inactive')]);
        }

        return $next($request);
    }
}
