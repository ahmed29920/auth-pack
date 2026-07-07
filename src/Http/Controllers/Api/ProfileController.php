<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Ashtech\LaravelAuthKit\Http\Resources\UserResource;
use Ashtech\LaravelAuthKit\Services\AuthService;
use Ashtech\LaravelAuthKit\Support\ApiResponse;

class ProfileController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function show()
    {
        $user = request()->user();

        return ApiResponse::success([
            'user' => new UserResource($user),
        ]);
    }
}
