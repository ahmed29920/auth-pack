<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use AhmedAshraf\Auth\Http\Requests\Api\LoginRequest;
use AhmedAshraf\Auth\Services\AuthService;
use AhmedAshraf\Auth\Support\ApiResponse;

class LoginController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function store(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated(), 'api');

        return ApiResponse::success($result, 'Login successful');
    }

    public function destroy()
    {
        $this->authService->logout(request()->user(), 'api');

        return ApiResponse::success(null, 'Logged out successfully');
    }
}
