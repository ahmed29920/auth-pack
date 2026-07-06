@props([
    'model' => null,
    'englishLabel' => null,
    'arabicLabel' => null,
    'englishRequired' => true,
])

@php
    use AhmedAshraf\Auth\Support\TranslationMapper;

    $englishLabel ??= __('kango-auth::fields.name_en');
    $arabicLabel ??= __('kango-auth::fields.name_ar');
@endphp

<x-kango-auth::input
    :label="$englishLabel"
    name="name"
    :value="old('name', TranslationMapper::translation($model, 'name', 'en') ?? '')"
    :required="$englishRequired"
/>
<x-kango-auth::input
    :label="$arabicLabel"
    name="name_ar"
    :value="old('name_ar', TranslationMapper::translation($model, 'name', 'ar') ?? '')"
/>
