<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Tests\Feature;

use Ashtech\LaravelAuthKit\Tests\TestCase;

class RegisterTest extends TestCase
{
    public function test_user_can_register_via_api(): void
    {
        $response = $this->postJson($this->apiPrefix('register'), [
            'name' => 'Test User',
            'phone' => '+201234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'phone', 'role'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'phone' => '+201234567890',
            'role' => 'customer',
        ]);
    }

    public function test_registration_rejects_disallowed_role(): void
    {
        $response = $this->postJson($this->apiPrefix('register'), [
            'name' => 'Admin User',
            'phone' => '+201111111111',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_vendor_registration_is_disabled_by_default(): void
    {
        $response = $this->postJson($this->apiPrefix('register'), [
            'name' => 'Vendor User',
            'phone' => '+201222222222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'vendor',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_vendor_can_register_when_enabled(): void
    {
        config([
            'laravel-auth-kit.vendor_registration_enabled' => true,
            'laravel-auth-kit.registration_allowed_roles' => ['customer', 'vendor'],
        ]);

        $response = $this->postJson($this->apiPrefix('register'), [
            'name' => 'Vendor User',
            'phone' => '+201222222222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'vendor',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'phone' => '+201222222222',
            'role' => 'vendor',
        ]);
    }
}
