# Laravel Slugify Package

The `jetcod\laravel-slugify` package simplifies the generation and management of slugs for Eloquent models in Laravel applications. This package utilizes the `HasSlug` trait to automatically create and update slugs based on your model's attributes, with flexible configuration options.

## Installation

### Requirements

- PHP ^7.4|^8.0
- Laravel >8.0

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
        return SlugOptions::create();
    }
}
```

## Available Configuration Options

- **generateSlugsOnCreate(bool)**: Set to true if you want to generate slugs on model creation.
- **generateSlugsOnUpdate(bool)**: Set to true if you want to regenerate slugs when the model is updated.
- **slugColumn(string)**: Define the column name where the slugs will be stored. The defined column type should be `json`.
- **slugSeparator(string)**: Character separator between words in the slug. (Default value is '-')
- **maximumLength(int)**: Maximum character length of the slug.

## **Example**: Creating and Updating a Model with Slug Generation

When you create or update a model with HasSlug configured, the slug is automatically generated and saved according to the options you've specified.

```php
$post = new Post();
$post->title = "This is an Example Title";
$post->save();

// $post->slugs will be like {"title":"this-is-an-example-title"} (based on the configured options)
```

## Testing

Run your tests to verify that slugs are generated as expected:

```bash
php artisan test
```

## License

This package is open-source software licensed under the [MIT License](LICENSE).

