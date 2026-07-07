<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Ashtech\LaravelAuthKit\Exceptions\InvalidOtpException;
use Ashtech\LaravelAuthKit\Http\Requests\Api\VerifyContactRequest;
use Ashtech\LaravelAuthKit\Services\VerificationService;
use Ashtech\LaravelAuthKit\Support\AuthRedirect;

class VerificationController extends Controller
{
    public function __construct(
        protected VerificationService $verificationService,
    ) {}

    public function show(Request $request)
    {
        $user = $request->user();

        if (! $user->needsVerification()) {
            return redirect()->to(AuthRedirect::homeFor($user));
        }

        return view('laravel-auth-kit::auth.verify', [
            'user' => $user,
            'pending' => $user->pendingVerificationChannels(),
        ]);
    }

    public function verifyEmail(VerifyContactRequest $request)
    {
        try {
            $this->verificationService->verifyEmail($request->user(), $request->validated('code'));
        } catch (InvalidOtpException $e) {
            throw ValidationException::withMessages(['email_code' => [$e->getMessage()]]);
        }

        return $this->redirectAfterVerification($request);
    }

    public function verifyPhone(VerifyContactRequest $request)
    {
        try {
            $this->verificationService->verifyPhone($request->user(), $request->validated('code'));
        } catch (InvalidOtpException $e) {
            throw ValidationException::withMessages(['phone_code' => [$e->getMessage()]]);
        }

        return $this->redirectAfterVerification($request);
    }

    public function resendEmail(Request $request)
    {
        $user = $request->user();

        if (! $user->needsEmailVerification()) {
            return back();
        }

        $this->verificationService->sendEmailVerification($user);

        return back()->with('status', __('laravel-auth-kit::auth.verify.email_sent'));
    }

    public function resendPhone(Request $request)
    {
        $user = $request->user();

        if (! $user->needsPhoneVerification()) {
            return back();
        }

        $this->verificationService->sendPhoneVerification($user);

        return back()->with('status', __('laravel-auth-kit::auth.verify.phone_sent'));
    }

    protected function redirectAfterVerification(Request $request)
    {
        $user = $request->user()->fresh();

        if ($user->needsVerification()) {
            return redirect()
                ->route('auth-kit.verify')
                ->with('status', __('laravel-auth-kit::auth.verify.partial_success'));
        }

        return redirect()
            ->to(AuthRedirect::homeFor($user))
            ->with('status', __('laravel-auth-kit::auth.verify.completed'));
    }
}
