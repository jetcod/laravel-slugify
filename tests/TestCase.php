<?php

namespace Jetcod\LaravelSlugify\Test;

use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    /**
     * @var Faker
     */
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker::create();

        $this->setUpDatabase($this->app);

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'Jetcod\\LaravelSlugify\\Test\\Factories\\' . class_basename($modelName) . 'Factory';
        });
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    protected function setUpDatabase(Application $app)
    {
        Schema::create('test_model', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('name')->nullable();
            $table->json('slugs')->nullable();
            $table->string('other_field')->nullable();
            $table->string('url')->nullable();
        });
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('translations.database', [
            'prefix'     => 'tbl_',
            'table_name' => 'translations',
        ]);
    }
}
