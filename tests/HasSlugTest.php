<?php

namespace Jetcod\LaravelSlugify\Test;

use Illuminate\Support\Collection;
use Jetcod\LaravelSlugify\SlugOptions;
use Jetcod\LaravelSlugify\Test\Fixtures\TestModel;

/**
 * @internal
 *
 * @coversNothing
 */
class HasSlugTest extends TestCase
{
    public function testGenerateSlugsAndSaveThemIntoTheSpecifiedColumnOnCreateAndUpdate()
    {
        $testModel = new class extends TestModel {
            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()
                    ->saveSlugsTo('slugs')
                ;
            }
        };
        $testModel->name = 'some text goes here';
        $testModel->save();

        $generatedSlugValue = ['name' => 'some-text-goes-here'];

        $this->assertEquals($generatedSlugValue, $testModel->slugs);
        $this->assertDatabaseHas('test_model', ['name' => 'some text goes here']);
        $this->assertDatabaseHas('test_model', ['slugs' => json_encode($generatedSlugValue)]);

        $storedModel       = $testModel::find($testModel->id);
        $storedModel->name = 'some other text goes here';
        $storedModel->save();

        $expectedSlugValue = ['name' => 'some-other-text-goes-here'];

        $this->assertEquals($expectedSlugValue, $storedModel->slugs);
        $this->assertDatabaseHas('test_model', ['name' => 'some other text goes here']);
        $this->assertDatabaseHas('test_model', ['slugs' => json_encode($expectedSlugValue)]);
    }

    public function testGenerateSlugsAndSaveThemIntoTheSpecifiedColumnOnlyOnCreate()
    {
        $testModel = new class extends TestModel {
            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()
                    ->saveSlugsTo('slugs')
                    ->doNotGenerateSlugsOnUpdate()
                ;
            }
        };
        $testModel->name = 'some text goes here';
        $testModel->save();

        $generatedSlugValue = ['name' => 'some-text-goes-here'];

        $this->assertEquals($generatedSlugValue, $testModel->slugs);
        $this->assertDatabaseHas('test_model', ['name' => 'some text goes here']);
        $this->assertDatabaseHas('test_model', ['slugs' => json_encode($generatedSlugValue)]);

        $storedModel       = $testModel::find($testModel->id);
        $storedModel->name = 'some other text goes here';
        $storedModel->save();

        $invalidSlugValue = ['name' => 'some-other-text-goes-here'];

        $this->assertEquals($generatedSlugValue, $storedModel->slugs);
        $this->assertDatabaseHas('test_model', ['name' => 'some other text goes here']);
        $this->assertDatabaseHas('test_model', ['slugs' => json_encode($generatedSlugValue)]);
        $this->assertDatabaseMissing('test_model', ['slugs' => json_encode($invalidSlugValue)]);
    }

    public function testGenerateSlugsAndSaveThemIntoTheSpecifiedColumnOnlyOnUpdate()
    {
        $testModel = new class extends TestModel {
            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()
                    ->saveSlugsTo('slugs')
                    ->doNotGenerateSlugsOnCreate()
                ;
            }
        };
        $testModel->name = 'some text goes here';
        $testModel->save();

        $this->assertNull($testModel->slugs);
        $this->assertDatabaseHas('test_model', ['name' => 'some text goes here']);
        $this->assertDatabaseMissing('test_model', ['slug' => json_encode(['name' => 'some-text-goes-here'])]);

        $storedModel       = $testModel::find($testModel->id);
        $storedModel->name = 'some other text goes here';
        $storedModel->save();

        $expectedSlugValue = ['name' => 'some-other-text-goes-here'];

        $this->assertEquals($expectedSlugValue, $storedModel->slugs);
        $this->assertDatabaseHas('test_model', ['name' => 'some other text goes here']);
        $this->assertDatabaseHas('test_model', ['slugs' => json_encode($expectedSlugValue)]);
    }

    public function testGeneratedSlugsAreLimitedByTheSpecifiedLength()
    {
        $testModel = new class extends TestModel {
            protected $sluggables = [
                'name',
                'title',
            ];

            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()
                    ->slugWithSeparator('_')
                    ->slugShouldBeNoLongerThan(10)
                    ->saveSlugsTo('slugs')
                ;
            }
        };
        $testModel->name  = $this->faker->sentence(20);
        $testModel->title = $this->faker->sentence(20);
        $testModel->save();

        dd(json_encode($testModel->slugs));

        foreach ($testModel->slugs as $value) {
            $this->assertLessThanOrEqual(10, strlen($value));
            $this->assertStringEndsNotWith('_', $value);
        }

        $this->assertCount(2, $testModel->slugs);
    }

    public function testItGetsAccessToSlugsColumnDataAsCollectionIfItIsNotCasted()
    {
        $testModel = new class extends TestModel {
            protected $sluggables = [
                'name',
                'title',
            ];

            protected $casts = [];

            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()->saveSlugsTo('slugs');
            }
        };
        $testModel->name  = $this->faker->sentence(10);
        $testModel->title = $this->faker->sentence(10);
        $testModel->save();

        $this->assertCount(2, $testModel->slugs);
        $this->assertDatabaseHas('test_model', ['slugs' => json_encode($testModel->slugs)]);
        $this->assertInstanceOf(Collection::class, $testModel->slugs);
    }
}
