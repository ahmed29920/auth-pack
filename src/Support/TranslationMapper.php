<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Support;

final class TranslationMapper
{
    /**
     * @return list<string>
     */
    public static function locales(): array
    {
        return array_values((array) config('laravel-auth-kit.translatable_locales', ['en', 'ar']));
    }

    /**
     * @return array<string, string>
     */
    public static function fromLegacyFields(mixed $primary, mixed $arabic = null): array
    {
        if (is_array($primary)) {
            return array_filter($primary, fn ($value) => $value !== null && $value !== '');
        }

        return array_filter([
            'en' => is_string($primary) ? $primary : null,
            'ar' => is_string($arabic) ? $arabic : null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mergeIntoPayload(array $data, string $field, ?string $legacyArabicField = null): array
    {
        $legacyArabicField ??= $field.'_ar';

        if (isset($data[$field]) && is_array($data[$field])) {
            unset($data[$legacyArabicField]);

            return $data;
        }

        if (array_key_exists($field, $data) || array_key_exists($legacyArabicField, $data)) {
            $data[$field] = self::fromLegacyFields(
                $data[$field] ?? null,
                $data[$legacyArabicField] ?? null,
            );
        }

        unset($data[$legacyArabicField]);

        return $data;
    }

    /**
     * @param  array<string, string>  $translations
     * @return array<string, string>
     */
    public static function forApi(array $translations): array
    {
        return $translations;
    }

    public static function translation(mixed $model, string $attribute, string $locale): ?string
    {
        if (! is_object($model) || ! method_exists($model, 'getTranslation')) {
            return null;
        }

        $value = $model->getTranslation($attribute, $locale, false);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function formatAttribute(mixed $model, string $attribute): array
    {
        if (! is_object($model) || ! method_exists($model, 'getTranslations')) {
            return [
                $attribute => null,
                "{$attribute}_translations" => [],
            ];
        }

        $translations = $model->getTranslations($attribute);
        $locale = app()->getLocale();
        $fallback = (string) config('laravel-auth-kit.fallback_locale', 'en');

        return [
            $attribute => $translations[$locale] ?? $translations[$fallback] ?? reset($translations) ?: null,
            "{$attribute}_translations" => $translations,
            "{$attribute}_ar" => $translations['ar'] ?? null,
        ];
    }
}
