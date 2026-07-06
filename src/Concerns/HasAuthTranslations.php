<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Concerns;

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
        return (string) config('auth-package.fallback_locale', config('app.fallback_locale', 'en'));
    }
}
