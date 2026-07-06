@props(['type' => 'error'])

@php
    $styles = match ($type) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800/50 dark:bg-emerald-950/50 dark:text-emerald-200',
        'info' => 'border-indigo-200 bg-indigo-50 text-indigo-800 dark:border-indigo-800/50 dark:bg-indigo-950/50 dark:text-indigo-200',
        default => 'border-red-200 bg-red-50 text-red-800 dark:border-red-800/50 dark:bg-red-950/50 dark:text-red-200',
    };
@endphp

<div {{ $attributes->merge(['class' => "mb-5 rounded-xl border px-4 py-3 text-sm {$styles}"]) }}>
    {{ $slot }}
</div>

