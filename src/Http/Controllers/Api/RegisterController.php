<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Ashtech\LaravelAuthKit\Http\Requests\Api\RegisterRequest;
use Ashtech\LaravelAuthKit\Services\AuthService;
use Ashtech\LaravelAuthKit\Support\ApiResponse;

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
