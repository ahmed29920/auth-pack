@extends('laravel-auth-kit::layouts.guest')

@php
    use Ashtech\LaravelAuthKit\Support\AuthMethods;

    $viaEmail = AuthMethods::allowsEmailPasswordReset();
    $viaPhone = AuthMethods::allowsPhonePasswordReset();

    $subtitle = match (true) {
        $viaEmail && ! $viaPhone => __('laravel-auth-kit::auth.forgot.subtitle_email'),
        $viaPhone && ! $viaEmail => __('laravel-auth-kit::auth.forgot.subtitle_phone'),
        default => __('laravel-auth-kit::auth.forgot.subtitle'),
    };
@endphp

@section('title', __('laravel-auth-kit::auth.forgot.title').' — '.config('app.name'))

@section('content')
    <x-laravel-auth-kit::card>
        <x-laravel-auth-kit::auth-header
            :title="__('laravel-auth-kit::auth.forgot.title')"
            :subtitle="$subtitle"
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

        @if (! $viaEmail && ! $viaPhone)
            <x-laravel-auth-kit::alert type="info">
                {{ __('laravel-auth-kit::auth.forgot.no_methods') }}
            </x-laravel-auth-kit::alert>
        @else
            <form method="POST" action="{{ route('auth-kit.password.forgot.store') }}" class="space-y-1">
                @csrf

                @if ($viaEmail)
                    <x-laravel-auth-kit::input
                        :label="__('laravel-auth-kit::auth.fields.email')"
                        name="email"
                        type="email"
                        :placeholder="__('laravel-auth-kit::auth.placeholders.email')"
                        :required="! $viaPhone"
                    />
                @endif

                @if ($viaPhone)
                    <x-laravel-auth-kit::input
                        :label="__('laravel-auth-kit::auth.fields.phone')"
                        name="phone"
                        type="tel"
                        :placeholder="__('laravel-auth-kit::auth.placeholders.phone')"
                        :required="! $viaEmail"
                    />
                @endif

                <div class="mt-2">
                <x-laravel-auth-kit::button>
                    {{ $viaPhone && ! $viaEmail
                        ? __('laravel-auth-kit::auth.forgot.submit_otp')
                        : __('laravel-auth-kit::auth.forgot.submit_email') }}
                </x-laravel-auth-kit::button>
                </div>
            </form>
        @endif

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('auth-kit.login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                {{ __('laravel-auth-kit::auth.forgot.back_to_login') }}
            </a>
        </p>
    </x-laravel-auth-kit::card>
@endsection
