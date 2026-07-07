@extends('laravel-auth-kit::layouts.guest')

@php
    use Ashtech\LaravelAuthKit\Support\AuthMethods;
@endphp

@section('title', __('laravel-auth-kit::auth.register.title').' — '.config('app.name'))

@section('content')
    <x-laravel-auth-kit::card>
        <x-laravel-auth-kit::auth-header
            :title="__('laravel-auth-kit::auth.register.heading')"
            :subtitle="__('laravel-auth-kit::auth.register.subtitle')"
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

        <form method="POST" action="{{ route('auth-kit.register.store') }}" class="space-y-1">
            @csrf

            <x-laravel-auth-kit::input
                :label="__('laravel-auth-kit::auth.fields.name')"
                name="name"
                :placeholder="__('laravel-auth-kit::auth.placeholders.name')"
                required
            />

            @if (AuthMethods::emailForPassword() || AuthMethods::emailForOtp())
                <x-laravel-auth-kit::input
                    :label="__('laravel-auth-kit::auth.fields.email')"
                    name="email"
                    type="email"
                    :placeholder="__('laravel-auth-kit::auth.placeholders.email')"
                    :required="! AuthMethods::phoneForPassword() && ! AuthMethods::phoneForOtp()"
                />
            @endif

            @if (AuthMethods::phoneForPassword() || AuthMethods::phoneForOtp())
                <x-laravel-auth-kit::input
                    :label="__('laravel-auth-kit::auth.fields.phone')"
                    name="phone"
                    type="tel"
                    :placeholder="__('laravel-auth-kit::auth.placeholders.phone')"
                    :required="! AuthMethods::emailForPassword() && ! AuthMethods::emailForOtp()"
                />
            @endif

            <x-laravel-auth-kit::input
                :label="__('laravel-auth-kit::auth.fields.password')"
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

            @if (count(config('laravel-auth-kit.registration_allowed_roles', [])) > 1)
                <div class="mb-4">
                    <label for="role" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ __('laravel-auth-kit::auth.register.account_type') }}
                    </label>
                    <select
                        id="role"
                        name="role"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                    >
                        @foreach (config('laravel-auth-kit.registration_allowed_roles') as $role)
                            <option value="{{ $role }}" @selected(old('role', config('laravel-auth-kit.default_role')) === $role)>
                                {{ __('laravel-auth-kit::auth.roles.'.$role) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <x-laravel-auth-kit::button>{{ __('laravel-auth-kit::auth.register.submit') }}</x-laravel-auth-kit::button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ __('laravel-auth-kit::auth.register.has_account') }}
            <a href="{{ route('auth-kit.login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                {{ __('laravel-auth-kit::auth.register.login_link') }}
            </a>
        </p>
    </x-laravel-auth-kit::card>
@endsection
