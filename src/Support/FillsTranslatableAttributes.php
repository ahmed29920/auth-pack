<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Support;

use Illuminate\Database\Eloquent\Model;

trait FillsTranslatableAttributes
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function extractTranslatableAttributes(Model $model, array $data): array
    {
        if (! method_exists($model, 'getTranslatableAttributes')) {
            return $data;
        }

        foreach ($model->getTranslatableAttributes() as $attribute) {
            $translations = $this->resolveTranslations($attribute, $data);

            if ($translations === null) {
                continue;
            }

            $model->setTranslations($attribute, $translations);
            $model->setAttribute($attribute, $model->getTranslations($attribute));
            unset($data[$attribute], $data["{$attribute}_ar"]);

            foreach (TranslationMapper::locales() as $locale) {
                unset($data["{$attribute}.{$locale}"]);
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>|null
     */
    protected function resolveTranslations(string $attribute, array $data): ?array
    {
        if (isset($data[$attribute]) && is_array($data[$attribute])) {
            return array_filter($data[$attribute], fn ($value) => $value !== null && $value !== '');
        }

        $translations = [];

        foreach (TranslationMapper::locales() as $locale) {
            $key = "{$attribute}.{$locale}";

            if (array_key_exists($key, $data) && is_string($data[$key]) && $data[$key] !== '') {
                $translations[$locale] = $data[$key];
            }
        }

        if ($translations !== []) {
            return $translations;
        }

        $legacyArabic = "{$attribute}_ar";

        if (array_key_exists($attribute, $data) || array_key_exists($legacyArabic, $data)) {
            return TranslationMapper::fromLegacyFields(
                $data[$attribute] ?? null,
                $data[$legacyArabic] ?? null,
            );
        }

        return null;
    }
}
