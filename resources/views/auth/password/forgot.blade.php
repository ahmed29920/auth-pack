@extends('kango-auth::layouts.guest')

@php
    use AhmedAshraf\Auth\Support\AuthMethods;

    $viaEmail = AuthMethods::allowsEmailPasswordReset();
    $viaPhone = AuthMethods::allowsPhonePasswordReset();

    $subtitle = match (true) {
        $viaEmail && ! $viaPhone => __('kango-auth::auth.forgot.subtitle_email'),
        $viaPhone && ! $viaEmail => __('kango-auth::auth.forgot.subtitle_phone'),
        default => __('kango-auth::auth.forgot.subtitle'),
    };
@endphp

@section('title', __('kango-auth::auth.forgot.title').' — '.config('app.name'))

@section('content')
    <x-kango-auth::card>
        <x-kango-auth::auth-header
            :title="__('kango-auth::auth.forgot.title')"
            :subtitle="$subtitle"
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

        @if (! $viaEmail && ! $viaPhone)
            <x-kango-auth::alert type="info">
                {{ __('kango-auth::auth.forgot.no_methods') }}
            </x-kango-auth::alert>
        @else
            <form method="POST" action="{{ route('kango.auth.password.forgot.store') }}" class="space-y-1">
                @csrf

                @if ($viaEmail)
                    <x-kango-auth::input
                        :label="__('kango-auth::auth.fields.email')"
                        name="email"
                        type="email"
                        :placeholder="__('kango-auth::auth.placeholders.email')"
                        :required="! $viaPhone"
                    />
                @endif

                @if ($viaPhone)
                    <x-kango-auth::input
                        :label="__('kango-auth::auth.fields.phone')"
                        name="phone"
                        type="tel"
                        :placeholder="__('kango-auth::auth.placeholders.phone')"
                        :required="! $viaEmail"
                    />
                @endif

                <div class="mt-2">
                <x-kango-auth::button>
                    {{ $viaPhone && ! $viaEmail
                        ? __('kango-auth::auth.forgot.submit_otp')
                        : __('kango-auth::auth.forgot.submit_email') }}
                </x-kango-auth::button>
                </div>
            </form>
        @endif

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('kango.auth.login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                {{ __('kango-auth::auth.forgot.back_to_login') }}
            </a>
        </p>
    </x-kango-auth::card>
@endsection
