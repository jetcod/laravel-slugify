# Laravel Slugify Package

![Build](https://img.shields.io/github/actions/workflow/status/jetcod/laravel-slugify/tests.yml?style=for-the-badge)


[![Latest Stable Version](https://img.shields.io/packagist/v/jetcod/laravel-slugify?label=Latest%20Stable%20Version)](https://packagist.org/packages/jetcod/laravel-slugify)
[![Total Downloads](https://img.shields.io/packagist/dt/jetcod/laravel-slugify?label=Total%20Downloads)](https://packagist.org/packages/jetcod/laravel-slugify)
[![License](https://img.shields.io/github/license/jetcod/laravel-slugify?label=License)](https://github.com/jetcod/laravel-slugify/blob/main/LICENSE)

## Overview

The `jetcod\laravel-slugify` package simplifies the generation and management of slugs for Eloquent models in Laravel applications. This package utilizes the `HasSlug` trait to automatically create and update slugs based on your model's attributes, with flexible configuration options.

## Installation

### Requirements

- PHP ^7.4 | ^8.0
- Laravel 8.0+

### Step 1: Install via Composer

To install the package, run the following command:

```bash
composer require jetcod/laravel-slugify
```

### Step 2: Configure your model

In any model where you want to use the slugging functionality, use the `HasSlug` trait and implement `getSlugConfig()` method to configure the slug options.


# Usage

## Setting Up the Sluggable Model

To start using slugs in your model, follow these steps:

- **Use the Trait**: In your model, use the `HasSlug` trait.
- **Define Slug Configuration**: Implement the `getSlugConfig()` method in your model. This method should return a SlugOptions object where you define your sluggable configuration.
- **Set the `$sluggables` property**: Define an array of model attributes that should be used to generate the slug.

Here’s an example:

```php
use Jetcod\LaravelSlugify\SlugOptions;
use Jetcod\LaravelSlugify\Traits\HasSlug;

class YourModel extends Model
{
    use HasSlug;

    protected $casts = [
        'slugs' => 'array', // Optional: Cast the 'slugs' attribute to an array
    ];

    protected $sluggables = ['a_column_name'];  // Specify columns to be sluggified

    protected function getSlugConfig(): SlugOptions
    {
        return SlugOptions::create()
            ->slugColumn('slugs')   // Define the column name where the slugs will be stored
        ;
    }
}
```

## Available Configuration Options

- **doNotGenerateSlugsOnCreate()**: Call this method if you want to avoid generating slugs on model creation.
- **doNotGenerateSlugsOnUpdate()**: Call this method if you want to avoid generating slugs when the model is updated.
- **slugColumn(string)**: Define the column name where the slugs will be stored. The defined column type should be `json`.
- **slugSeparator(string)**: Character separator between words in the slug. (Default value is '-')
- **maximumLength(int)**: Maximum character length of the slug.
- **avoidDuplicates()**: Call this method to generate unique slugs.

> **Note**: Calling slugColumn() is required while defining the slug options.

## **Example**: Creating and Updating a Model with Slug Generation

When you create or update a model with HasSlug configured, the slug is automatically generated and saved according to the options you've specified.

```php
$post = new Post();
$post->title = "This is an Example Title";
$post->save();

// $post->slugs will be like {"title":"this-is-an-example-title"} (based on the configured options)
```

## **Example**: Generating Unique Slugs
To ensure unique slugs, you can use the `avoidDuplicates()` method in your SlugOptions:

```php
use Jetcod\LaravelSlugify\SlugOptions;
use Jetcod\LaravelSlugify\Traits\HasSlug;

class YourModel extends Model
{
    use HasSlug;

    protected $sluggables = ['title'];

    protected function getSlugConfig(): SlugOptions
    {
        return SlugOptions::create()
            ->slugColumn('slugs')
            ->avoidDuplicates()
        ;
    }
}
```

Then when you create or update a model, the slug will be generated and saved as a unique value.

```php
$post = new Post();
$post->title = "This is an Example Title";
$post->save();

// Result: $post->slugs will be like {"title":"this-is-an-example-title"} 

// Create another post with the same title
$post = new Post();
$post->title = "This is an Example Title";
$post->save();

// Result: $post->slugs will be like {"title":"this-is-an-example-title-1"}
```

> **Note**: It also considers the maximum length of the slug while generating unique slug strings.

## Testing

Run your tests to verify that slugs are generated as expected:

```bash
composer test
```

Run code coverage analysis to generate a coverage report. This will generate a coverage report in the `coverage` directory.

```bash
composer coverage
```

Run PHPStan to check for potential issues in the code:

```bash
composer phpstan
```

## License

This package is open-source software licensed under the [MIT License](LICENSE).

