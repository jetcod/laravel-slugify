<?php

namespace Jetcod\LaravelSlugify\Test\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jetcod\LaravelSlugify\SlugOptions;
use Jetcod\LaravelSlugify\Traits\HasSlug;

class TestModel extends Model
{
    use HasSlug;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'slugs' => 'array',
    ];

    protected $table = 'test_model';

    protected $sluggables = ['name'];

    protected function getSlugConfig(): SlugOptions
    {
        return SlugOptions::make();
    }
}
