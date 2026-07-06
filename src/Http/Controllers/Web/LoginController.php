<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use AhmedAshraf\Auth\Http\Requests\Api\LoginRequest;
use AhmedAshraf\Auth\Services\AuthService;
use AhmedAshraf\Auth\Support\AuthRedirect;

class LoginController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function create()
    {
        return view('kango-auth::auth.login');
    }

    public function store(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated(), 'web');

        if ($result['verification_required'] ?? false) {
            return redirect()->route('kango.auth.verify');
        }

        return redirect()->intended(AuthRedirect::homeFor($request->user()));
    }

    public function destroy()
    {
        $this->authService->logout(request()->user(), 'web');

        return redirect(config('auth-package.web.redirect_after_logout', '/auth/login'));
    }
}
