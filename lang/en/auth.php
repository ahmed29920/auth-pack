<?php

declare(strict_types=1);

return [
    'language' => 'Language',

    'login' => [
        'title' => 'Welcome back',
        'subtitle' => 'Sign in to continue to your account',
        'submit' => 'Sign in',
        'with_password' => 'Password',
        'with_otp' => 'Verification code',
        'remember' => 'Remember me',
        'forgot' => 'Forgot password?',
        'no_account' => "Don't have an account?",
        'register' => 'Create account',
        'otp_hint' => 'Request a verification code first via the OTP API before signing in.',
    ],

    'register' => [
        'title' => 'Create account',
        'heading' => 'Create a new account',
        'subtitle' => 'Enter your details to join the platform',
        'submit' => 'Create account',
        'has_account' => 'Already have an account?',
        'login_link' => 'Sign in',
        'account_type' => 'Account type',
    ],

    'account' => [
        'inactive' => 'Your account is inactive. Please contact support.',
    ],

    'profile' => [
        'title' => 'Profile',
        'heading' => 'Profile',
        'subtitle' => 'Your current account details',
        'email' => 'Email',
        'phone' => 'Phone',
        'email_verification' => 'Email verification',
        'phone_verification' => 'Phone verification',
        'verified' => 'Verified',
        'not_verified' => 'Not verified',
        'logout' => 'Sign out',
    ],

    'reset' => [
        'title' => 'Reset password',
        'heading' => 'New password',
        'subtitle' => 'Enter your new password',
        'email_label' => 'Email:',
        'submit' => 'Save password',
        'back_to_login' => 'Back to login',
        'otp_heading' => 'Verify code',
        'otp_subtitle' => 'Enter the verification code and your new password',
        'phone_label' => 'Phone:',
        'resend_otp' => 'Resend code',
        'password_changed' => 'Your password has been changed. You can sign in now.',
    ],

    'forgot' => [
        'title' => 'Reset password',
        'subtitle' => 'Enter your registered email or phone number',
        'subtitle_email' => 'Enter your registered email address',
        'subtitle_phone' => 'Enter your registered phone number',
        'submit_email' => 'Send reset link',
        'submit_otp' => 'Send verification code',
        'back_to_login' => 'Back to login',
        'otp_sent' => 'A verification code has been sent to your phone.',
        'no_methods' => 'Password recovery is not enabled in configuration.',
        'invalid_method' => 'This recovery method is not available.',
    ],

    'fields' => [
        'name' => 'Full name',
        'email' => 'Email address',
        'phone' => 'Phone number',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'new_password' => 'New password',
        'otp_code' => 'Verification code (OTP)',
    ],

    'placeholders' => [
        'name' => 'John Doe',
        'email' => 'name@example.com',
        'phone' => '01xxxxxxxxx',
        'password' => 'At least 8 characters',
        'password_confirmation' => 'Re-enter your password',
        'otp' => '000000',
    ],

    'verify' => [
        'title' => 'Verify account',
        'heading' => 'Verify your account',
        'subtitle' => 'Enter the verification code we sent to continue',
        'email_target' => 'Email:',
        'phone_target' => 'Phone:',
        'email_section' => 'Email verification',
        'phone_section' => 'Phone verification',
        'confirm_email' => 'Verify email',
        'confirm_phone' => 'Verify phone',
        'resend_email' => 'Resend email code',
        'resend_phone' => 'Resend phone code',
        'sent_after_register' => 'We sent a verification code. Please verify to continue.',
        'email_sent' => 'A new verification code was sent to your email.',
        'phone_sent' => 'A new verification code was sent to your phone.',
        'partial_success' => 'Verified successfully. Complete any remaining verification steps.',
        'completed' => 'Your account is verified. Welcome!',
    ],

    'otp' => [
        'throttled' => 'Please wait :seconds seconds before requesting another code.',
        'channel_disabled' => 'This OTP channel is not enabled.',
        'reset_not_allowed' => 'Phone password reset is not enabled.',
    ],

    'mail' => [
        'footer' => 'All rights reserved.',
        'greeting' => 'Hello :name,',
        'otp_intro' => 'Use the verification code below to :purpose.',
        'otp_expires' => 'This code expires on :time.',
        'otp_security_note' => 'If you did not request this code, you can safely ignore this email.',
        'reset_intro' => 'We received a request to reset your password. Click the button below to choose a new password.',
        'reset_button' => 'Reset password',
        'reset_expires' => 'This link expires in :minutes minutes.',
        'reset_copy_link' => 'If the button does not work, copy and paste this link into your browser:',
        'reset_password_subject' => 'Reset your :app password',
        'otp_subjects' => [
            'login' => 'Your sign-in code',
            'register' => 'Welcome — verify your account',
            'reset_password' => 'Password reset code',
            'verify_email' => 'Verify your email address',
            'verify_phone' => 'Verify your phone number',
        ],
        'otp_purposes' => [
            'login' => 'sign in to your account',
            'register' => 'complete your registration',
            'reset_password' => 'reset your password',
            'verify_email' => 'verify your email address',
            'verify_phone' => 'verify your phone number',
        ],
    ],

    'roles' => [
        'customer' => 'Customer',
        'vendor' => 'Vendor',
        'super_admin' => 'Super admin',
        'admin' => 'Admin',
        'vendor_staff' => 'Vendor staff',
        'delivery' => 'Delivery',
    ],
];
