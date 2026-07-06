<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use AhmedAshraf\Auth\Http\Requests\Api\RegisterRequest;
use AhmedAshraf\Auth\Services\AuthService;
use AhmedAshraf\Auth\Support\AuthRedirect;

class RegisterController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function create()
    {
        return view('kango-auth::auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated(), 'web');

        if ($result['verification_required'] ?? false) {
            return redirect()
                ->route('kango.auth.verify')
                ->with('status', __('kango-auth::auth.verify.sent_after_register'));
        }

        return redirect()->intended(AuthRedirect::homeFor($request->user()));
    }
}
