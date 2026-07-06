<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use AhmedAshraf\Auth\Http\Requests\Api\RegisterRequest;
use AhmedAshraf\Auth\Services\AuthService;
use AhmedAshraf\Auth\Support\ApiResponse;

class RegisterController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function store(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::success($result, 'Registration successful', 201);
    }
}
