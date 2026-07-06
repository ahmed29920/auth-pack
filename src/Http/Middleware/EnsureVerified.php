<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use AhmedAshraf\Auth\Exceptions\VerificationRequiredException;
use AhmedAshraf\Auth\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerified
{
    /**
     * @var list<string>
     */
    protected array $exceptRouteNames = [
        'kango.auth.verify',
        'kango.auth.verify.email',
        'kango.auth.verify.phone',
        'kango.auth.verify.email.resend',
        'kango.auth.verify.phone.resend',
        'kango.auth.logout',
        'kango.auth.locale',
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

        return redirect()->route('kango.auth.verify');
    }
}
