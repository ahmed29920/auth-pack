@extends('laravel-auth-kit::layouts.guest')

@section('title', __('laravel-auth-kit::auth.reset.title').' — '.config('app.name'))

@section('content')
    <x-laravel-auth-kit::card>
        <x-laravel-auth-kit::auth-header
            :title="__('laravel-auth-kit::auth.reset.otp_heading')"
            :subtitle="__('laravel-auth-kit::auth.reset.otp_subtitle')"
        />

        @if ($errors->any())
            <x-laravel-auth-kit::alert>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-laravel-auth-kit::alert>
        @endif

        <form method="POST" action="{{ route('auth-kit.password.reset-phone.store') }}">
            @csrf
            <input type="hidden" name="phone" value="{{ $phone }}">

            <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-800/50">
                <span class="text-slate-500 dark:text-slate-400">{{ __('laravel-auth-kit::auth.reset.phone_label') }}</span>
                <span class="ms-2 font-medium text-slate-900 dark:text-slate-100" dir="ltr">{{ $phone }}</span>
            </div>

            <x-laravel-auth-kit::input
                :label="__('laravel-auth-kit::auth.fields.otp_code')"
                name="otp_code"
                type="text"
                :placeholder="__('laravel-auth-kit::auth.placeholders.otp')"
                inputmode="numeric"
                autocomplete="one-time-code"
                required
            />
            <x-laravel-auth-kit::input
                :label="__('laravel-auth-kit::auth.fields.new_password')"
                name="password"
                type="password"
                :placeholder="__('laravel-auth-kit::auth.placeholders.password')"
                required
            />
            <x-laravel-auth-kit::input
                :label="__('laravel-auth-kit::auth.fields.password_confirmation')"
                name="password_confirmation"
                type="password"
                :placeholder="__('laravel-auth-kit::auth.placeholders.password_confirmation')"
                required
            />

            <x-laravel-auth-kit::button>{{ __('laravel-auth-kit::auth.reset.submit') }}</x-laravel-auth-kit::button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('auth-kit.password.forgot') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                {{ __('laravel-auth-kit::auth.reset.resend_otp') }}
            </a>
        </p>
    </x-laravel-auth-kit::card>
@endsection
