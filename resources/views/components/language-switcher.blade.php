@php
    $locales = config('laravel-auth-kit.available_locales', []);
    $current = app()->getLocale();
@endphp

@if (count($locales) > 1)
    <div
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 rounded-xl border border-slate-200/80 bg-white/90 p-1 shadow-sm backdrop-blur-sm dark:border-slate-700/80 dark:bg-slate-900/90']) }}
        role="group"
        aria-label="{{ __('laravel-auth-kit::auth.language') }}"
    >
        @foreach ($locales as $code => $label)
            <a
                href="{{ route('auth-kit.locale', $code) }}"
                @class([
                    'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                    'bg-indigo-600 text-white shadow-sm' => $current === $code,
                    'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => $current !== $code,
                ])
                title="{{ $label }}"
            >
                {{ strtoupper($code) }}
            </a>
        @endforeach
    </div>
@endif

