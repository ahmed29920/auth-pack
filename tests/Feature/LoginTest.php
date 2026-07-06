<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Tests\Feature;

use AhmedAshraf\Auth\Models\User;
use AhmedAshraf\Auth\Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    public function test_user_can_login_with_phone_and_password(): void
    {
        User::query()->create([
            'name' => 'Login User',
            'phone' => '+201234567890',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->postJson($this->apiPrefix('login'), [
            'phone' => '+201234567890',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.phone', '+201234567890')
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::query()->create([
            'name' => 'Login User',
            'phone' => '+201234567890',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->postJson($this->apiPrefix('login'), [
            'phone' => '+201234567890',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::query()->create([
            'name' => 'Inactive User',
            'phone' => '+201333333333',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => false,
        ]);

        $response = $this->postJson($this->apiPrefix('login'), [
            'phone' => '+201333333333',
            'password' => 'password123',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', __('kango-auth::auth.account.inactive'));
    }
}
