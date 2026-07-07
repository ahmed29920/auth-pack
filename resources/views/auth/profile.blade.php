@extends('laravel-auth-kit::layouts.guest')

@section('title', __('laravel-auth-kit::auth.profile.title').' — '.config('app.name'))

@section('content')
    <x-laravel-auth-kit::card>
        <x-laravel-auth-kit::auth-header
            :title="__('laravel-auth-kit::auth.profile.heading')"
            :subtitle="__('laravel-auth-kit::auth.profile.subtitle')"
        />

        <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-8">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 text-2xl font-bold text-white backdrop-blur">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
                <h2 class="mt-4 text-xl font-bold text-white">{{ $user->name }}</h2>
                <p class="text-indigo-100">{{ __('laravel-auth-kit::auth.roles.'.$user->getAuthRole()) }}</p>
            </div>
            <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                <div class="flex justify-between gap-4 px-6 py-3 text-sm">
                    <dt class="text-slate-500 dark:text-slate-400">{{ __('laravel-auth-kit::auth.profile.email') }}</dt>
                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $user->email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 px-6 py-3 text-sm">
                    <dt class="text-slate-500 dark:text-slate-400">{{ __('laravel-auth-kit::auth.profile.phone') }}</dt>
                    <dd class="font-medium text-slate-900 dark:text-slate-100" dir="ltr">{{ $user->phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 px-6 py-3 text-sm">
                    <dt class="text-slate-500 dark:text-slate-400">{{ __('laravel-auth-kit::auth.profile.email_verification') }}</dt>
                    <dd>
                        @if ($user->isVerifiedEmail())
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">{{ __('laravel-auth-kit::auth.profile.verified') }}</span>
                        @else
                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">{{ __('laravel-auth-kit::auth.profile.not_verified') }}</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between gap-4 px-6 py-3 text-sm">
                    <dt class="text-slate-500 dark:text-slate-400">{{ __('laravel-auth-kit::auth.profile.phone_verification') }}</dt>
                    <dd>
                        @if ($user->isVerifiedPhone())
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">{{ __('laravel-auth-kit::auth.profile.verified') }}</span>
                        @else
                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">{{ __('laravel-auth-kit::auth.profile.not_verified') }}</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <form method="POST" action="{{ route('auth-kit.logout') }}">
            @csrf
            <x-laravel-auth-kit::button variant="secondary">{{ __('laravel-auth-kit::auth.profile.logout') }}</x-laravel-auth-kit::button>
        </form>
    </x-laravel-auth-kit::card>
@endsection
