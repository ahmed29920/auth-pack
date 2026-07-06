@extends('kango-auth::layouts.guest')

@php
    use AhmedAshraf\Auth\Support\AuthMethods;
@endphp

@section('title', __('kango-auth::auth.register.title').' — '.config('app.name'))

@section('content')
    <x-kango-auth::card>
        <x-kango-auth::auth-header
            :title="__('kango-auth::auth.register.heading')"
            :subtitle="__('kango-auth::auth.register.subtitle')"
        />

        @if ($errors->any())
            <x-kango-auth::alert>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-kango-auth::alert>
        @endif

        <form method="POST" action="{{ route('kango.auth.register.store') }}" class="space-y-1">
            @csrf

            <x-kango-auth::input
                :label="__('kango-auth::auth.fields.name')"
                name="name"
                :placeholder="__('kango-auth::auth.placeholders.name')"
                required
            />

            @if (AuthMethods::emailForPassword() || AuthMethods::emailForOtp())
                <x-kango-auth::input
                    :label="__('kango-auth::auth.fields.email')"
                    name="email"
                    type="email"
                    :placeholder="__('kango-auth::auth.placeholders.email')"
                    :required="! AuthMethods::phoneForPassword() && ! AuthMethods::phoneForOtp()"
                />
            @endif

            @if (AuthMethods::phoneForPassword() || AuthMethods::phoneForOtp())
                <x-kango-auth::input
                    :label="__('kango-auth::auth.fields.phone')"
                    name="phone"
                    type="tel"
                    :placeholder="__('kango-auth::auth.placeholders.phone')"
                    :required="! AuthMethods::emailForPassword() && ! AuthMethods::emailForOtp()"
                />
            @endif

            <x-kango-auth::input
                :label="__('kango-auth::auth.fields.password')"
                name="password"
                type="password"
                :placeholder="__('kango-auth::auth.placeholders.password')"
                required
            />
            <x-kango-auth::input
                :label="__('kango-auth::auth.fields.password_confirmation')"
                name="password_confirmation"
                type="password"
                :placeholder="__('kango-auth::auth.placeholders.password_confirmation')"
                required
            />

            @if (count(config('auth-package.registration_allowed_roles', [])) > 1)
                <div class="mb-4">
                    <label for="role" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ __('kango-auth::auth.register.account_type') }}
                    </label>
                    <select
                        id="role"
                        name="role"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                    >
                        @foreach (config('auth-package.registration_allowed_roles') as $role)
                            <option value="{{ $role }}" @selected(old('role', config('auth-package.default_role')) === $role)>
                                {{ __('kango-auth::auth.roles.'.$role) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <x-kango-auth::button>{{ __('kango-auth::auth.register.submit') }}</x-kango-auth::button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ __('kango-auth::auth.register.has_account') }}
            <a href="{{ route('kango.auth.login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                {{ __('kango-auth::auth.register.login_link') }}
            </a>
        </p>
    </x-kango-auth::card>
@endsection
