<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use AhmedAshraf\Auth\Exceptions\InvalidOtpException;
use AhmedAshraf\Auth\Http\Requests\Api\VerifyContactRequest;
use AhmedAshraf\Auth\Services\VerificationService;
use AhmedAshraf\Auth\Support\AuthRedirect;

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

        return view('kango-auth::auth.verify', [
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

        return back()->with('status', __('kango-auth::auth.verify.email_sent'));
    }

    public function resendPhone(Request $request)
    {
        $user = $request->user();

        if (! $user->needsPhoneVerification()) {
            return back();
        }

        $this->verificationService->sendPhoneVerification($user);

        return back()->with('status', __('kango-auth::auth.verify.phone_sent'));
    }

    protected function redirectAfterVerification(Request $request)
    {
        $user = $request->user()->fresh();

        if ($user->needsVerification()) {
            return redirect()
                ->route('kango.auth.verify')
                ->with('status', __('kango-auth::auth.verify.partial_success'));
        }

        return redirect()
            ->to(AuthRedirect::homeFor($user))
            ->with('status', __('kango-auth::auth.verify.completed'));
    }
}
