<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Ashtech\LaravelAuthKit\Http\Requests\Api\LoginRequest;
use Ashtech\LaravelAuthKit\Services\AuthService;
use Ashtech\LaravelAuthKit\Support\AuthRedirect;

class LoginController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function create()
    {
        return view('laravel-auth-kit::auth.login');
    }

    public function store(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated(), 'web');

        if ($result['verification_required'] ?? false) {
            return redirect()->route('auth-kit.verify');
        }

        return redirect()->intended(AuthRedirect::homeFor($request->user()));
    }

    public function destroy()
    {
        $this->authService->logout(request()->user(), 'web');

        return redirect(config('laravel-auth-kit.web.redirect_after_logout', '/auth/login'));
    }
}
