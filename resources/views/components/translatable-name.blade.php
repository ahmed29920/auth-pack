@props([
    'model' => null,
    'englishLabel' => null,
    'arabicLabel' => null,
    'englishRequired' => true,
])

@php
    use Ashtech\LaravelAuthKit\Support\TranslationMapper;

    $englishLabel ??= __('laravel-auth-kit::fields.name_en');
    $arabicLabel ??= __('laravel-auth-kit::fields.name_ar');
@endphp

<x-laravel-auth-kit::input
    :label="$englishLabel"
    name="name"
    :value="old('name', TranslationMapper::translation($model, 'name', 'en') ?? '')"
    :required="$englishRequired"
/>
<x-laravel-auth-kit::input
    :label="$arabicLabel"
    name="name_ar"
    :value="old('name_ar', TranslationMapper::translation($model, 'name', 'ar') ?? '')"
/>
