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
        $slugString = Str::slug(
            $this->{$attribute},
            $this->slugOptions->slugSeparator
        );

        if (strlen($slugString) > $this->slugOptions->maximumLength) {
            $slugString = Str::limit($slugString, $this->slugOptions->maximumLength, '');
        }

        return rtrim($slugString, $this->slugOptions->slugSeparator);
    }
}
