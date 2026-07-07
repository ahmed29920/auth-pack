<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Concerns;

use Spatie\Translatable\HasTranslations;

trait HasAuthTranslations
{
    use HasTranslations;

    public function getTranslationLocale(): string
    {
        return app()->getLocale();
    }

    public function getFallbackLocale(): string
    {
        return (string) config('laravel-auth-kit.fallback_locale', config('app.fallback_locale', 'en'));
    }
}
