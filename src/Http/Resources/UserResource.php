<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use AhmedAshraf\Auth\Contracts\RoleManagerInterface;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roleManager = app(RoleManagerInterface::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $roleManager->getRole($this->resource),
            'roles' => $roleManager->getRoles($this->resource),
            'vendor_id' => $this->vendor_id,
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
