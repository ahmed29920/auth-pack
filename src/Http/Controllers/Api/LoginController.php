<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Ashtech\LaravelAuthKit\Http\Requests\Api\LoginRequest;
use Ashtech\LaravelAuthKit\Services\AuthService;
use Ashtech\LaravelAuthKit\Support\ApiResponse;

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
