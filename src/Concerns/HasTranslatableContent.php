<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Concerns;

use Spatie\Translatable\HasTranslations;

/**
 * Wrapper trait for Spatie's translatable functionality.
 *
 * Provides translatable content support with additional conveniences
 * specific to lara-content models.
 *
 * Usage:
 *   use HasTranslatableContent;
 *
 *   public array $translatable = ['title', 'content'];
 */
trait HasTranslatableContent
{
    use HasTranslations;

    /**
     * Get the content in a specific locale or fallback to default.
     */
    public function getTranslatedContent(string $attribute, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        return $this->getTranslation($attribute, $locale)
            ?? $this->getTranslation($attribute, config('app.fallback_locale'));
    }

    /**
     * Check if content exists for a specific locale.
     */
    public function hasTranslation(string $attribute, string $locale): bool
    {
        $translations = $this->getTranslations($attribute);

        return isset($translations[$locale]) && ! empty($translations[$locale]);
    }

    /**
     * Get all available locales for a translatable attribute.
     *
     * @return array<string>
     */
    public function getAvailableLocales(string $attribute): array
    {
        return array_keys($this->getTranslations($attribute));
    }

    /**
     * Set content for multiple locales at once.
     *
     * @param  array<string, string>  $translations  Keyed by locale
     */
    public function setTranslations(string $attribute, array $translations): self
    {
        foreach ($translations as $locale => $value) {
            $this->setTranslation($attribute, $locale, $value);
        }

        return $this;
    }
}
