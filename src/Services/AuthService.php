<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Ashtech\LaravelAuthKit\Contracts\RoleManagerInterface;
use Ashtech\LaravelAuthKit\Contracts\UserRepositoryInterface;
use Ashtech\LaravelAuthKit\Enums\OtpChannel;
use Ashtech\LaravelAuthKit\Enums\OtpPurpose;
use Ashtech\LaravelAuthKit\Exceptions\AccountInactiveException;
use Ashtech\LaravelAuthKit\Exceptions\AuthenticationException;
use Ashtech\LaravelAuthKit\Models\User;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected RoleManagerInterface $roleManager,
        protected OtpService $otpService,
        protected VerificationService $verificationService,
    ) {}

    public function register(array $data, string $client = 'api'): array
    {
        $role = $data['role'] ?? config('laravel-auth-kit.default_role', 'customer');

        if (! in_array($role, config('laravel-auth-kit.registration_allowed_roles', ['customer']), true)) {
            throw new \InvalidArgumentException('Role not allowed for registration.');
        }

        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'] ?? null,
            'vendor_id' => $data['vendor_id'] ?? null,
            'is_active' => true,
        ]);

        $this->roleManager->assign($user, $role);

        if ($user->needsVerification()) {
            $this->verificationService->dispatchPendingVerifications($user);
        }

        return $this->buildAuthResponse($user, $client);
    }

    public function login(array $data, string $client = 'api'): array
    {
        if (! empty($data['otp_code'])) {
            return $this->loginWithOtp($data, $client);
        }

        $user = $this->resolveUserForLogin($data);

        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            throw new AuthenticationException();
        }

        $this->ensureActive($user);

        return $this->buildAuthResponse($user, $client);
    }

    public function loginWithOtp(array $data, string $client = 'api'): array
    {
        $identifier = $data['phone'] ?? $data['email'] ?? null;

        if (! $identifier) {
            throw new AuthenticationException('Phone or email is required for OTP login.');
        }

        $channel = isset($data['phone']) ? OtpChannel::Phone : OtpChannel::Email;

        $this->otpService->verify($identifier, $channel, OtpPurpose::Login, $data['otp_code']);

        $user = $channel === OtpChannel::Phone
            ? $this->userRepository->findByPhone($identifier)
            : $this->userRepository->findByEmail($identifier);

        if (! $user) {
            throw new AuthenticationException();
        }

        $this->ensureActive($user);
        $this->markVerifiedFromOtpLogin($user, $channel);

        return $this->buildAuthResponse($user, $client);
    }

    public function logout(?User $user = null, string $client = 'api'): void
    {
        if ($client === 'api' && $user) {
            $user->currentAccessToken()?->delete();

            return;
        }

        Auth::guard('web')->logout();
    }

    public function me(User $user): array
    {
        return $this->formatUser($user);
    }

    protected function resolveUserForLogin(array $data): ?User
    {
        if (! empty($data['email']) && config('laravel-auth-kit.methods.email_password')) {
            return $this->userRepository->findByEmail($data['email']);
        }

        if (! empty($data['phone']) && config('laravel-auth-kit.methods.phone_password')) {
            return $this->userRepository->findByPhone($data['phone']);
        }

        return null;
    }

    protected function buildAuthResponse(User $user, string $client): array
    {
        $payload = [
            'user' => $this->formatUser($user),
            'verification_required' => $user->needsVerification(),
            'pending_verification' => $user->pendingVerificationChannels(),
        ];

        if ($client === 'api' && config('laravel-auth-kit.clients.api')) {
            $tokenName = config('laravel-auth-kit.sanctum.token_name', 'auth_token');
            $abilities = config('laravel-auth-kit.sanctum.abilities', ['*']);
            $payload['token'] = $user->createToken($tokenName, $abilities)->plainTextToken;
        }

        if ($client === 'web' && config('laravel-auth-kit.clients.web')) {
            Auth::guard('web')->login($user);
        }

        return $payload;
    }

    protected function markVerifiedFromOtpLogin(User $user, OtpChannel $channel): void
    {
        if ($channel === OtpChannel::Phone && $user->phone && ! $user->isVerifiedPhone()) {
            $this->userRepository->markPhoneVerified($user);
            $user->refresh();
        }

        if ($channel === OtpChannel::Email && $user->email && ! $user->isVerifiedEmail()) {
            $this->userRepository->markEmailVerified($user);
            $user->refresh();
        }
    }

    protected function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $this->roleManager->getRole($user),
            'roles' => $this->roleManager->getRoles($user),
            'vendor_id' => $user->vendor_id,
            'email_verified_at' => $user->email_verified_at,
            'phone_verified_at' => $user->phone_verified_at,
            'is_active' => $user->is_active,
        ];
    }

    protected function ensureActive(User $user): void
    {
        if (! $user->is_active) {
            throw new AccountInactiveException();
        }
    }
}
