<?php

namespace App\Support;

trait HasLocalizedFields
{
    public function localized(string $field, ?string $fallbackField = null): ?string
    {
        $locale = app()->getLocale();
        $enField = $field.'_en';
        if ($locale === 'en' && isset($this->attributes[$enField]) && filled($this->{$enField})) {
            return (string) $this->{$enField};
        }

        $base = $fallbackField ?? $field;

        return isset($this->attributes[$base]) ? (string) $this->{$base} : null;
    }

    public function localizedName(): string
    {
        return (string) ($this->localized('name') ?: $this->name ?: '');
    }
}
