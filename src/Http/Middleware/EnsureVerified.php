<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ashtech\LaravelAuthKit\Exceptions\VerificationRequiredException;
use Ashtech\LaravelAuthKit\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerified
{
    /**
     * @var list<string>
     */
    protected array $exceptRouteNames = [
        'auth-kit.verify',
        'auth-kit.verify.email',
        'auth-kit.verify.phone',
        'auth-kit.verify.email.resend',
        'auth-kit.verify.phone.resend',
        'auth-kit.logout',
        'auth-kit.locale',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->needsVerification()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, $this->exceptRouteNames, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return ApiResponse::error(
                'Account verification is required before you can continue.',
                403,
                ['pending_verification' => $user->pendingVerificationChannels()],
            );
        }

        return redirect()->route('auth-kit.verify');
    }
}
