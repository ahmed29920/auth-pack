@extends('laravel-auth-kit::layouts.guest')

@php
    use Ashtech\LaravelAuthKit\Support\AuthMethods;

    $showTabs = AuthMethods::showLoginModeTabs();
    $defaultMode = old('login_mode', $showTabs ? 'password' : (AuthMethods::allowsOtpLogin() ? 'otp' : 'password'));
    $otpLength = config('laravel-auth-kit.otp.length', 6);
@endphp

@section('title', __('laravel-auth-kit::auth.login.title').' — '.config('app.name'))

@push('styles')
    <style>
        #login-mode-password:checked ~ .login-panels #panel-password { display: block; }
        #login-mode-password:checked ~ .login-panels #panel-otp { display: none; }
        #login-mode-otp:checked ~ .login-panels #panel-password { display: none; }
        #login-mode-otp:checked ~ .login-panels #panel-otp { display: block; }
        #login-mode-password:checked ~ .login-tabs label[for="login-mode-password"],
        #login-mode-otp:checked ~ .login-tabs label[for="login-mode-otp"] {
            background-color: rgb(79 70 229);
            color: white;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        }
        .login-panel { display: none; }
        @unless($showTabs)
            #panel-password { display: {{ AuthMethods::allowsPasswordLogin() ? 'block' : 'none' }}; }
            #panel-otp { display: {{ AuthMethods::allowsOtpLogin() && ! AuthMethods::allowsPasswordLogin() ? 'block' : 'none' }}; }
        @endunless
    </style>
@endpush

@section('content')
    <x-laravel-auth-kit::card>
        <x-laravel-auth-kit::auth-header
            :title="__('laravel-auth-kit::auth.login.title')"
            :subtitle="__('laravel-auth-kit::auth.login.subtitle')"
        />

        @if (session('status'))
            <x-laravel-auth-kit::alert type="success">{{ session('status') }}</x-laravel-auth-kit::alert>
        @endif

        @if ($errors->any())
            <x-laravel-auth-kit::alert>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-laravel-auth-kit::alert>
        @endif

        <form method="POST" action="{{ route('auth-kit.login') }}">
            @csrf

            @if ($showTabs)
                <input type="radio" name="login_mode" id="login-mode-password" value="password" class="sr-only" @checked($defaultMode === 'password')>
                <input type="radio" name="login_mode" id="login-mode-otp" value="otp" class="sr-only" @checked($defaultMode === 'otp')>

                <div class="login-tabs mb-5 flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                    <label for="login-mode-password" class="flex-1 cursor-pointer rounded-lg px-3 py-2 text-center text-sm font-medium text-slate-600 transition dark:text-slate-300">
                        {{ __('laravel-auth-kit::auth.login.with_password') }}
                    </label>
                    <label for="login-mode-otp" class="flex-1 cursor-pointer rounded-lg px-3 py-2 text-center text-sm font-medium text-slate-600 transition dark:text-slate-300">
                        {{ __('laravel-auth-kit::auth.login.with_otp') }}
                    </label>
                </div>
            @else
                <input type="hidden" name="login_mode" value="{{ AuthMethods::allowsPasswordLogin() ? 'password' : 'otp' }}">
            @endif

            <div class="login-panels space-y-1">
                {{-- Password login panel --}}
                @if (AuthMethods::allowsPasswordLogin())
                    <div id="panel-password" class="login-panel">
                        @if (AuthMethods::emailForPassword())
                            <x-laravel-auth-kit::input
                                :label="__('laravel-auth-kit::auth.fields.email')"
                                name="email"
                                type="email"
                                :placeholder="__('laravel-auth-kit::auth.placeholders.email')"
                                :required="! AuthMethods::phoneForPassword()"
                            />
                        @endif

                        @if (AuthMethods::phoneForPassword())
                            <x-laravel-auth-kit::input
                                :label="__('laravel-auth-kit::auth.fields.phone')"
                                name="phone"
                                type="tel"
                                :placeholder="__('laravel-auth-kit::auth.placeholders.phone')"
                                :required="! AuthMethods::emailForPassword()"
                            />
                        @endif

                        <x-laravel-auth-kit::input
                            :label="__('laravel-auth-kit::auth.fields.password')"
                            name="password"
                            type="password"
                            :placeholder="__('laravel-auth-kit::auth.placeholders.password')"
                        />

                        <div class="mb-4 flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20">
                                {{ __('laravel-auth-kit::auth.login.remember') }}
                            </label>
                            <a href="{{ route('auth-kit.password.forgot') }}" class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                {{ __('laravel-auth-kit::auth.login.forgot') }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- OTP login panel --}}
                @if (AuthMethods::allowsOtpLogin())
                    <div id="panel-otp" class="login-panel">
                        @if (AuthMethods::emailForOtp())
                            <x-laravel-auth-kit::input
                                :label="__('laravel-auth-kit::auth.fields.email')"
                                name="email"
                                type="email"
                                :placeholder="__('laravel-auth-kit::auth.placeholders.email')"
                                :required="! AuthMethods::phoneForOtp()"
                            />
                        @endif

                        @if (AuthMethods::phoneForOtp())
                            <x-laravel-auth-kit::input
                                :label="__('laravel-auth-kit::auth.fields.phone')"
                                name="phone"
                                type="tel"
                                :placeholder="__('laravel-auth-kit::auth.placeholders.phone')"
                                :required="! AuthMethods::emailForOtp()"
                            />
                        @endif

                        <x-laravel-auth-kit::input
                            :label="__('laravel-auth-kit::auth.fields.otp_code')"
                            name="otp_code"
                            type="text"
                            :placeholder="__('laravel-auth-kit::auth.placeholders.otp')"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            :required="true"
                            maxlength="{{ $otpLength }}"
                        />

                        <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('laravel-auth-kit::auth.login.otp_hint') }}
                        </p>
                    </div>
                @endif
            </div>

            <x-laravel-auth-kit::button>{{ __('laravel-auth-kit::auth.login.submit') }}</x-laravel-auth-kit::button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ __('laravel-auth-kit::auth.login.no_account') }}
            <a href="{{ route('auth-kit.register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                {{ __('laravel-auth-kit::auth.login.register') }}
            </a>
        </p>
    </x-laravel-auth-kit::card>
@endsection

@if ($showTabs)
    @push('scripts')
        <script>
            document.querySelector('form[action="{{ route('auth-kit.login') }}"]')?.addEventListener('submit', function () {
                const mode = document.querySelector('input[name="login_mode"]:checked')?.value ?? 'password';
                const activeId = mode === 'otp' ? 'panel-otp' : 'panel-password';

                document.querySelectorAll('.login-panel').forEach(function (panel) {
                    const disable = panel.id !== activeId;
                    panel.querySelectorAll('input, select, textarea').forEach(function (el) {
                        if (el.name !== 'login_mode') {
                            el.disabled = disable;
                        }
                    });
                });
            });
        </script>
    @endpush
@endif


