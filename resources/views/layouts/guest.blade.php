<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('styles')
</head>
<body class="min-h-screen font-sans antialiased bg-gradient-to-br from-slate-50 via-white to-indigo-50 text-slate-900 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950 dark:text-slate-100">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-24 {{ app()->getLocale() === 'ar' ? '-left-24' : '-right-24' }} h-72 w-72 rounded-full bg-indigo-400/20 blur-3xl"></div>
        <div class="absolute -bottom-24 {{ app()->getLocale() === 'ar' ? '-right-24' : '-left-24' }} h-72 w-72 rounded-full bg-violet-400/20 blur-3xl"></div>
    </div>

    <div class="fixed top-4 z-20 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }}">
        <x-laravel-auth-kit::language-switcher />
    </div>

    <div class="relative flex min-h-screen items-center justify-center p-4 pt-16 sm:p-6 sm:pt-20">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
