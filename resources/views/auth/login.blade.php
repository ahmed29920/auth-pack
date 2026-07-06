@extends('kango-auth::layouts.guest')

@php
    use AhmedAshraf\Auth\Support\AuthMethods;

    $showTabs = AuthMethods::showLoginModeTabs();
    $defaultMode = old('login_mode', $showTabs ? 'password' : (AuthMethods::allowsOtpLogin() ? 'otp' : 'password'));
    $otpLength = config('auth-package.otp.length', 6);
@endphp

@section('title', __('kango-auth::auth.login.title').' — '.config('app.name'))

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
    <x-kango-auth::card>
        <x-kango-auth::auth-header
            :title="__('kango-auth::auth.login.title')"
            :subtitle="__('kango-auth::auth.login.subtitle')"
        />

        @if (session('status'))
            <x-kango-auth::alert type="success">{{ session('status') }}</x-kango-auth::alert>
        @endif

        @if ($errors->any())
            <x-kango-auth::alert>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-kango-auth::alert>
        @endif

        <form method="POST" action="{{ route('kango.auth.login') }}">
            @csrf

            @if ($showTabs)
                <input type="radio" name="login_mode" id="login-mode-password" value="password" class="sr-only" @checked($defaultMode === 'password')>
                <input type="radio" name="login_mode" id="login-mode-otp" value="otp" class="sr-only" @checked($defaultMode === 'otp')>

                <div class="login-tabs mb-5 flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                    <label for="login-mode-password" class="flex-1 cursor-pointer rounded-lg px-3 py-2 text-center text-sm font-medium text-slate-600 transition dark:text-slate-300">
                        {{ __('kango-auth::auth.login.with_password') }}
                    </label>
                    <label for="login-mode-otp" class="flex-1 cursor-pointer rounded-lg px-3 py-2 text-center text-sm font-medium text-slate-600 transition dark:text-slate-300">
                        {{ __('kango-auth::auth.login.with_otp') }}
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
                            <x-kango-auth::input
                                :label="__('kango-auth::auth.fields.email')"
                                name="email"
                                type="email"
                                :placeholder="__('kango-auth::auth.placeholders.email')"
                                :required="! AuthMethods::phoneForPassword()"
                            />
                        @endif

                        @if (AuthMethods::phoneForPassword())
                            <x-kango-auth::input
                                :label="__('kango-auth::auth.fields.phone')"
                                name="phone"
                                type="tel"
                                :placeholder="__('kango-auth::auth.placeholders.phone')"
                                :required="! AuthMethods::emailForPassword()"
                            />
                        @endif

                        <x-kango-auth::input
                            :label="__('kango-auth::auth.fields.password')"
                            name="password"
                            type="password"
                            :placeholder="__('kango-auth::auth.placeholders.password')"
                        />

                        <div class="mb-4 flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20">
                                {{ __('kango-auth::auth.login.remember') }}
                            </label>
                            <a href="{{ route('kango.auth.password.forgot') }}" class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                {{ __('kango-auth::auth.login.forgot') }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- OTP login panel --}}
                @if (AuthMethods::allowsOtpLogin())
                    <div id="panel-otp" class="login-panel">
                        @if (AuthMethods::emailForOtp())
                            <x-kango-auth::input
                                :label="__('kango-auth::auth.fields.email')"
                                name="email"
                                type="email"
                                :placeholder="__('kango-auth::auth.placeholders.email')"
                                :required="! AuthMethods::phoneForOtp()"
                            />
                        @endif

                        @if (AuthMethods::phoneForOtp())
                            <x-kango-auth::input
                                :label="__('kango-auth::auth.fields.phone')"
                                name="phone"
                                type="tel"
                                :placeholder="__('kango-auth::auth.placeholders.phone')"
                                :required="! AuthMethods::emailForOtp()"
                            />
                        @endif

                        <x-kango-auth::input
                            :label="__('kango-auth::auth.fields.otp_code')"
                            name="otp_code"
                            type="text"
                            :placeholder="__('kango-auth::auth.placeholders.otp')"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            :required="true"
                            maxlength="{{ $otpLength }}"
                        />

                        <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('kango-auth::auth.login.otp_hint') }}
                        </p>
                    </div>
                @endif
            </div>

            <x-kango-auth::button>{{ __('kango-auth::auth.login.submit') }}</x-kango-auth::button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ __('kango-auth::auth.login.no_account') }}
            <a href="{{ route('kango.auth.register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                {{ __('kango-auth::auth.login.register') }}
            </a>
        </p>
    </x-kango-auth::card>
@endsection

@if ($showTabs)
    @push('scripts')
        <script>
            document.querySelector('form[action="{{ route('kango.auth.login') }}"]')?.addEventListener('submit', function () {
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


