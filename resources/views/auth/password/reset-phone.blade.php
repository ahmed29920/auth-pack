@extends('kango-auth::layouts.guest')

@section('title', __('kango-auth::auth.reset.title').' — '.config('app.name'))

@section('content')
    <x-kango-auth::card>
        <x-kango-auth::auth-header
            :title="__('kango-auth::auth.reset.otp_heading')"
            :subtitle="__('kango-auth::auth.reset.otp_subtitle')"
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

        <form method="POST" action="{{ route('kango.auth.password.reset-phone.store') }}">
            @csrf
            <input type="hidden" name="phone" value="{{ $phone }}">

            <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-800/50">
                <span class="text-slate-500 dark:text-slate-400">{{ __('kango-auth::auth.reset.phone_label') }}</span>
                <span class="ms-2 font-medium text-slate-900 dark:text-slate-100" dir="ltr">{{ $phone }}</span>
            </div>

            <x-kango-auth::input
                :label="__('kango-auth::auth.fields.otp_code')"
                name="otp_code"
                type="text"
                :placeholder="__('kango-auth::auth.placeholders.otp')"
                inputmode="numeric"
                autocomplete="one-time-code"
                required
            />
            <x-kango-auth::input
                :label="__('kango-auth::auth.fields.new_password')"
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

            <x-kango-auth::button>{{ __('kango-auth::auth.reset.submit') }}</x-kango-auth::button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('kango.auth.password.forgot') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                {{ __('kango-auth::auth.reset.resend_otp') }}
            </a>
        </p>
    </x-kango-auth::card>
@endsection
