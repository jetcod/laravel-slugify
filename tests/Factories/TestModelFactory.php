<?php

namespace Jetcod\LaravelSlugify\Test\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jetcod\LaravelSlugify\Test\Fixtures\TestModel;

class TestModelFactory extends Factory
{
    protected $model = TestModel::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'title' => $this->faker->sentence,
        ];
    }
}