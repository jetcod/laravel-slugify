<?php

namespace Jetcod\LaravelSlugify;

use Jetcod\DataTransport\AbstractDTO;

/**
 * @property string $slugColumn
 * @property string $slugSeparator
 * @property string $slugLanguage
 * @property int    $maximumLength
 * @property bool   $generateSlugsOnCreate
 * @property bool   $generateSlugsOnUpdate
 */
class SlugOptions extends AbstractDTO
{
    /** @var array|callable */
    public $generateSlugFrom;

    /**
     * Sets the field(s) that should be used to generate the slug.
     *
     * @param array|callable|string $fieldName the field name(s) to use for generating the slug
     */
    public function generateSlugFrom($fieldName): self
    {
        if (is_string($fieldName)) {
            $fieldName = [$fieldName];
        }

        $this->generateSlugFrom = $fieldName;

        return $this;
    }

    public function fromLocale(string $locale): self
    {
        $this->slugLanguage = \Locale::getPrimaryLanguage($locale);

        return $this;
    }

    public function fromLanguage(string $language): self
    {
        $this->slugLanguage = $language;

        return $this;
    }

    /**
     * Sets the column that should be used to store the generated slug.
     *
     * @param string $fieldName the name of the column to store the slug
     */
    public function saveSlugsTo(string $fieldName): self
    {
        $this->slugColumn = $fieldName;

        return $this;
    }

    /**
     * Sets the maximum length for the generated slug.
     *
     * @param int $maximumLength the maximum length for the generated slug
     */
    public function slugShouldBeNoLongerThan(int $maximumLength): self
    {
        $this->maximumLength = $maximumLength;

        return $this;
    }

    /**
     * Allows the generation of duplicate slugs.
     *
     * By default, the slug generation process will ensure that each slug is unique. This method disables that behavior,
     * allowing the generation of duplicate slugs.
     */
    public function allowDuplicatedSlugs(): self
    {
        $this->allowDuplicatedSlugs = true;

        return $this;
    }

    /**
     * Disables the automatic generation of slugs when a new record is created.
     *
     * By default, the slug generation process will automatically generate a slug when a new record is created.
     *
     * @return $this
     */
    public function doNotGenerateSlugsOnCreate(): self
    {
        $this->generateSlugsOnCreate = false;

        return $this;
    }

    /**
     * Disables the automatic generation of slugs when a record is updated.
     *
     * By default, the slug generation process will automatically generate a slug when a record is updated.
     *
     * @return $this
     */
    public function doNotGenerateSlugsOnUpdate(): self
    {
        $this->generateSlugsOnUpdate = false;

        return $this;
    }

    public function slugWithSeparator(string $separator): self
    {
        $this->slugSeparator = $separator;

        return $this;
    }

    public function generateSlugsOnUpdate(): self
    {
        $this->generateSlugsOnUpdate = true;

        return $this;
    }

    protected function init(): void
    {
        $this->slugColumn            = null;
        $this->slugSeparator         = '-';
        $this->slugLanguage          = 'en';
        $this->maximumLength         = 255;
        $this->generateSlugsOnCreate = true;
        $this->generateSlugsOnUpdate = true;
    }
}
