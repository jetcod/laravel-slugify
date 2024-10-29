<?php

namespace Jetcod\LaravelSlugify\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jetcod\LaravelSlugify\SlugOptions;

trait HasSlug
{
    /** @var SlugOptions */
    private $slugOptions;

    abstract protected function getSlugConfig(): SlugOptions;

    protected static function bootHasSlug(): void
    {
        static::creating(function (Model $model) {
            $model->generateSlugOnCreate();
        });

        static::updating(function (Model $model) {
            $model->generateSlugOnUpdate();
        });
    }

    protected function generateSlugOnCreate()
    {
        $this->slugOptions = $this->getSlugConfig();

        if (!$this->slugOptions->generateSlugsOnCreate) {
            return;
        }

        $this->saveSlugs();
    }

    protected function generateSlugOnUpdate()
    {
        $this->slugOptions = $this->getSlugConfig();

        if (!$this->slugOptions->generateSlugsOnUpdate) {
            return;
        }

        $this->saveSlugs();
    }

    protected function hasSluggableColumns(): bool
    {
        return !empty($this->getSluggables());
    }

    protected function saveSlugs(): void
    {
        $slugs = $this->generateSlugsCollection();

        if ($slugColumn = $this->slugOptions->slugColumn) {
            $this->{$slugColumn} = $slugs;
        }
    }

    protected function generateSlugsCollection(): Collection
    {
        if (!$this->hasSluggableColumns()) {
            return collect();
        }

        $collection = collect($this->sluggables);

        return $collection->combine(
            $collection->map(fn ($columnName) => $this->generateSlugString($columnName))
        );
    }

    private function getSluggables(): array
    {
        return property_exists($this, 'sluggables') ? $this->sluggables : [];
    }

    private function generateSlugString(string $attribute): string
    {
        if (empty($this->{$attribute})) {
            return '';
        }

        $slugString = Str::slug(
            $this->{$attribute},
            $this->slugOptions->slugSeparator
        );

        if (strlen($slugString) > $this->slugOptions->maximumLength) {
            $slugString = Str::limit($slugString, $this->slugOptions->maximumLength, '');
        }

        $slugString = rtrim($slugString, ' \n\r\t\v\0' . $this->slugOptions->slugSeparator);

        if ($this->slugOptions->unique) {
            return $this->unifySlugString($slugString, $attribute);
        }

        return $slugString;
    }

    private function unifySlugString(string $slugString, string $attribute): string
    {
        $suffix = static::select($this->slugOptions->slugColumn)
            ->where("{$this->slugOptions->slugColumn}", 'like', "%{$slugString}%")
            ->get()
            ->pluck($this->slugOptions->slugColumn)
            ->map(function ($value) use ($attribute) {
                $item = is_array($value) ? $value[$attribute] ?? null : json_decode($value)?->{$attribute};
                preg_match('/' . $this->slugOptions->slugSeparator . '(\d+)$/', $item, $matches);

                return (int) ($matches[1] ?? 0);
            })->max()
        ;

        // Increment the suffix if necessary
        $newSuffix     = is_int($suffix) ? $suffix + 1 : null;
        $newSlugString = null !== $newSuffix
            ? $slugString . $this->slugOptions->slugSeparator . $newSuffix
            : $slugString;

        // Check if the new slug string exceeds the maximum length and adjust if needed
        if (strlen($newSlugString) > $this->slugOptions->maximumLength) {
            $allowedLength = $this->slugOptions->maximumLength - strlen($this->slugOptions->slugSeparator . $newSuffix);
            $slugString    = substr($slugString, 0, $allowedLength);
            $slugString    = Str::limit($slugString, $allowedLength, '');
            $newSlugString = $slugString . $this->slugOptions->slugSeparator . $newSuffix;
        }

        // If adjusted string already exists, recurse
        return null !== $newSuffix ? $this->unifySlugString($newSlugString, $attribute) : $slugString;
    }
}
