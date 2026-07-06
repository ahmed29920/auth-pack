<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Requests\Concerns;

use AhmedAshraf\Auth\Support\TranslationMapper;

trait NormalizesTranslationInput
{
    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);

        if ($key !== null) {
            return $validated;
        }

        foreach (['name'] as $field) {
            if (is_array($this->input($field))) {
                $validated[$field] = $this->input($field);
            }
        }

        return $validated;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeTranslatableField('name', 'name_ar');
    }

    protected function normalizeTranslatableField(string $field, ?string $legacyArabicField = null): void
    {
        $legacyArabicField ??= $field.'_ar';

        if (is_array($this->input($field))) {
            return;
        }

        if (! $this->has($field) && ! $this->has($legacyArabicField)) {
            return;
        }

        $this->merge([
            $field => TranslationMapper::fromLegacyFields(
                $this->input($field),
                $this->input($legacyArabicField),
            ),
        ]);
    }

    /**
     * @return array<string, list<string|object>>
     */
    protected function translationFieldRules(string $field, bool $englishRequired = true, int $max = 255): array
    {
        $rules = [
            $field => [$englishRequired ? 'required' : 'nullable', 'array'],
        ];

        foreach (TranslationMapper::locales() as $locale) {
            $rules["{$field}.{$locale}"] = [
                $englishRequired && $locale === 'en' ? 'required' : 'nullable',
                'string',
                "max:{$max}",
            ];
        }

        return $rules;
    }
}
