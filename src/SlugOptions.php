<?php

namespace Jetcod\LaravelSlugify;

use Jetcod\DataTransport\AbstractDTO;

/**
 * @property string $slugColumn
 * @property string $slugSeparator
 * @property int    $maximumLength
 * @property bool   $generateSlugsOnCreate
 * @property bool   $generateSlugsOnUpdate
 */
class SlugOptions extends AbstractDTO
{
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

    /**
     * Sets the separator character to be used when generating slugs.
     *
     * By default, the slug separator is a hyphen (-). This method allows you to customize the separator character.
     *
     * @param string $separator the character to use as the slug separator
     *
     * @return $this
     */
    public function slugWithSeparator(string $separator): self
    {
        $this->slugSeparator = $separator;

        return $this;
    }

    protected function init(): void
    {
        $this->slugColumn            = null;
        $this->slugSeparator         = '-';
        $this->maximumLength         = 255;
        $this->generateSlugsOnCreate = true;
        $this->generateSlugsOnUpdate = true;
    }
}
