<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Tests\Feature;

use Illuminate\Support\Facades\Hash;
use AhmedAshraf\Auth\Models\Otp;
use AhmedAshraf\Auth\Models\User;
use AhmedAshraf\Auth\Tests\Support\CapturingSmsSender;
use AhmedAshraf\Auth\Tests\TestCase;

class OtpTest extends TestCase
{
    public function test_otp_is_sent_for_existing_user_login(): void
    {
        User::query()->create([
            'name' => 'OTP User',
            'phone' => '+201234567890',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->postJson($this->apiPrefix('otp/send'), [
            'phone' => '+201234567890',
            'purpose' => 'login',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull(CapturingSmsSender::$lastCode);
        $this->assertSame('+201234567890', CapturingSmsSender::$lastPhone);
        $this->assertDatabaseCount('otps', 1);
    }

    public function test_otp_is_not_sent_for_unknown_login_identifier(): void
    {
        $response = $this->postJson($this->apiPrefix('otp/send'), [
            'phone' => '+209999999999',
            'purpose' => 'login',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNull(CapturingSmsSender::$lastCode);
        $this->assertDatabaseCount('otps', 0);
    }

    public function test_otp_is_not_sent_for_register_when_user_exists(): void
    {
        User::query()->create([
            'name' => 'Existing User',
            'phone' => '+201234567890',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->postJson($this->apiPrefix('otp/send'), [
            'phone' => '+201234567890',
            'purpose' => 'register',
        ]);

        $response->assertOk();
        $this->assertNull(CapturingSmsSender::$lastCode);
        $this->assertDatabaseCount('otps', 0);
    }

    public function test_user_can_login_with_otp(): void
    {
        User::query()->create([
            'name' => 'OTP Login User',
            'phone' => '+201234567890',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->postJson($this->apiPrefix('otp/send'), [
            'phone' => '+201234567890',
            'purpose' => 'login',
        ]);

        $code = CapturingSmsSender::$lastCode;
        $this->assertNotNull($code);

        $response = $this->postJson($this->apiPrefix('otp/verify'), [
            'phone' => '+201234567890',
            'purpose' => 'login',
            'code' => $code,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_invalid_otp_increments_attempts(): void
    {
        User::query()->create([
            'name' => 'OTP User',
            'phone' => '+201234567890',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->postJson($this->apiPrefix('otp/send'), [
            'phone' => '+201234567890',
            'purpose' => 'login',
        ]);

        $this->postJson($this->apiPrefix('otp/verify'), [
            'phone' => '+201234567890',
            'purpose' => 'login',
            'code' => '000000',
        ])->assertUnprocessable();

        $otp = Otp::query()->first();
        $this->assertNotNull($otp);
        $this->assertSame(1, $otp->fresh()->attempts);
    }

    public function test_send_rejects_disabled_otp_channel(): void
    {
        config(['auth-package.methods.phone_otp' => false]);

        $response = $this->postJson($this->apiPrefix('otp/send'), [
            'phone' => '+201234567890',
            'purpose' => 'register',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_verify_rejects_authenticated_only_purpose(): void
    {
        $response = $this->postJson($this->apiPrefix('otp/verify'), [
            'phone' => '+201234567890',
            'purpose' => 'verify_email',
            'code' => '123456',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['purpose']);
    }
}
