<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Ashtech\LaravelAuthKit\Http\Requests\Api\RegisterRequest;
use Ashtech\LaravelAuthKit\Services\AuthService;
use Ashtech\LaravelAuthKit\Support\AuthRedirect;

class RegisterController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function create()
    {
        return view('laravel-auth-kit::auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated(), 'web');

        if ($result['verification_required'] ?? false) {
            return redirect()
                ->route('auth-kit.verify')
                ->with('status', __('laravel-auth-kit::auth.verify.sent_after_register'));
        }

        return redirect()->intended(AuthRedirect::homeFor($request->user()));
    }
}
