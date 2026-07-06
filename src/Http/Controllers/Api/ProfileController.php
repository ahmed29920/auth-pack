<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use AhmedAshraf\Auth\Http\Resources\UserResource;
use AhmedAshraf\Auth\Services\AuthService;
use AhmedAshraf\Auth\Support\ApiResponse;

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
