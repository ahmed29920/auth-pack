@props(['title', 'subtitle' => null])

<div class="mb-6 text-center">
    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-lg font-bold text-white shadow-lg shadow-indigo-600/30">
        {{ strtoupper(substr(config('app.name', 'K'), 0, 1)) }}
    </div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
    @if ($subtitle)
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
    @endif
</div>

