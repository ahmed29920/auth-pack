@extends('laravel-auth-kit::layouts.guest')

@section('title', __('laravel-auth-kit::auth.verify.title').' — '.config('app.name'))

@section('content')
    <x-laravel-auth-kit::card>
        <x-laravel-auth-kit::auth-header
            :title="__('laravel-auth-kit::auth.verify.heading')"
            :subtitle="__('laravel-auth-kit::auth.verify.subtitle')"
        />

        @if (session('status'))
            <x-laravel-auth-kit::alert type="success" class="mb-4">{{ session('status') }}</x-laravel-auth-kit::alert>
        @endif

        @if ($errors->any())
            <x-laravel-auth-kit::alert class="mb-4">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-laravel-auth-kit::alert>
        @endif

        <div class="mb-6 space-y-3 text-sm text-slate-600 dark:text-slate-400">
            @if (in_array('email', $pending, true) && $user->email)
                <p>
                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('laravel-auth-kit::auth.verify.email_target') }}</span>
                    <span class="ms-1 text-slate-900 dark:text-slate-100">{{ $user->email }}</span>
                </p>
            @endif
            @if (in_array('phone', $pending, true) && $user->phone)
                <p>
                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('laravel-auth-kit::auth.verify.phone_target') }}</span>
                    <span class="ms-1 text-slate-900 dark:text-slate-100" dir="ltr">{{ $user->phone }}</span>
                </p>
            @endif
        </div>

        @if (in_array('email', $pending, true))
            <div class="mb-6 border-b border-slate-200 pb-6 dark:border-slate-700">
                <p class="mb-3 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ __('laravel-auth-kit::auth.verify.email_section') }}</p>
                <form method="POST" action="{{ route('auth-kit.verify.email') }}" class="space-y-1">
                    @csrf
                    <x-laravel-auth-kit::input
                        :label="__('laravel-auth-kit::auth.fields.otp_code')"
                        name="code"
                        type="text"
                        :placeholder="__('laravel-auth-kit::auth.placeholders.otp')"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        required
                    />
                    <x-laravel-auth-kit::button>{{ __('laravel-auth-kit::auth.verify.confirm_email') }}</x-laravel-auth-kit::button>
                </form>
                <form method="POST" action="{{ route('auth-kit.verify.email.resend') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                        {{ __('laravel-auth-kit::auth.verify.resend_email') }}
                    </button>
                </form>
            </div>
        @endif

        @if (in_array('phone', $pending, true))
            <div class="space-y-1">
                <p class="mb-3 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ __('laravel-auth-kit::auth.verify.phone_section') }}</p>
                <form method="POST" action="{{ route('auth-kit.verify.phone') }}" class="space-y-1">
                    @csrf
                    <x-laravel-auth-kit::input
                        :label="__('laravel-auth-kit::auth.fields.otp_code')"
                        name="code"
                        type="text"
                        :placeholder="__('laravel-auth-kit::auth.placeholders.otp')"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        required
                    />
                    <x-laravel-auth-kit::button>{{ __('laravel-auth-kit::auth.verify.confirm_phone') }}</x-laravel-auth-kit::button>
                </form>
                <form method="POST" action="{{ route('auth-kit.verify.phone.resend') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                        {{ __('laravel-auth-kit::auth.verify.resend_phone') }}
                    </button>
                </form>
            </div>
        @endif

        <form method="POST" action="{{ route('auth-kit.logout') }}" class="mt-8 border-t border-slate-200 pt-6 dark:border-slate-700">
            @csrf
            <x-laravel-auth-kit::button variant="secondary">{{ __('laravel-auth-kit::auth.profile.logout') }}</x-laravel-auth-kit::button>
        </form>
    </x-laravel-auth-kit::card>
@endsection
