<?php

namespace Jetcod\LaravelSlugify\Test;

use Illuminate\Support\Collection;
use Jetcod\LaravelSlugify\SlugOptions;
use Jetcod\LaravelSlugify\Test\Fixtures\TestModel;

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

        foreach ($testModel->slugs as $value) {
            $this->assertLessThanOrEqual(10, strlen($value));
            $this->assertStringEndsNotWith('_', $value);
        }

        $this->assertCount(2, $testModel->slugs);
    }

    public function testGeneratedSlugsAreInLowercase()
    {
        $testModel = new class extends TestModel {
            protected $sluggables = ['name'];

            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()
                    ->saveSlugsTo('slugs')
                ;
            }
        };
        $testModel->name = $this->faker->sentence(10);
        $testModel->save();

        $this->assertEquals(strtolower($testModel->slugs['name']), $testModel->slugs['name']);
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

    public function testItGeneratesUniqiueSlugsOnDemand()
    {
        $model = new class extends TestModel {
            protected $sluggables = ['name', 'title'];

            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()
                    ->avoidDuplicates()
                    ->saveSlugsTo('slugs')
                ;
            }
        };

        $model->name  = $name = 'some text goes here';
        $model->title = $title = 'some title';
        $model->save();

        for ($i = 1; $i < 10; ++$i) {
            $testModel        = new $model();
            $testModel->name  = $name;
            $testModel->title = $title;
            $testModel->save();

            // Ensure slug is different
            $this->assertNotEquals($name, $testModel->slugs['name']);
            $this->assertNotEquals($title, $testModel->slugs['title']);

            // Ensure slug is unique in the database
            $this->assertDatabaseHas('test_model', ['name' => $name, 'slugs' => json_encode([
                'name'  => 'some-text-goes-here-' . $i,
                'title' => 'some-title-' . $i,
            ])]);
        }

        $this->assertDatabaseCount('test_model', 10);
    }

    public function testGeneratedSlugsAreTrimmed()
    {
        $model = new class extends TestModel {
            protected $sluggables = ['name', 'title'];

            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()
                    ->saveSlugsTo('slugs')
                ;
            }
        };

        $model->name  = 'some text with spaces at the end    ';
        $model->title = 'some title with hyphen at the end ---';
        $model->save();

        $this->assertEquals('some-text-with-spaces-at-the-end', $model->slugs['name']);
        $this->assertEquals('some-title-with-hyphen-at-the-end', $model->slugs['title']);
        $this->assertDatabaseHas('test_model', ['name' => $model->name, 'slugs' => json_encode([
            'name'  => 'some-text-with-spaces-at-the-end',
            'title' => 'some-title-with-hyphen-at-the-end',
        ])]);
    }

    public function testEmptySluggableAttributesAreIgnoredWithEnablingUniqueSlugsOption()
    {
        $model = new class extends TestModel {
            protected $sluggables = ['name', 'title'];

            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()
                    ->avoidDuplicates()
                    ->saveSlugsTo('slugs')
                ;
            }
        };

        $testModel       = new $model();
        $testModel->name = $name = 'some text goes here';
        $testModel->save();

        for ($i = 1; $i < 10; ++$i) {
            $testModel       = new $model();
            $testModel->name = $name;
            $testModel->save();

            $savedModel = $testModel::find($testModel->id);

            $this->assertEquals(['name' => 'some-text-goes-here-' . $i, 'title' => ''], $savedModel->slugs);
        }

        $this->assertDatabaseCount('test_model', 10);
    }

    public function testEmptySluggableAttributesAreIgnoredWithoutEnablingUniqueSlugsOption()
    {
        $model = new class extends TestModel {
            protected $sluggables = ['name', 'title'];

            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()
                    ->saveSlugsTo('slugs')
                ;
            }
        };

        $testModel       = new $model();
        $testModel->name = 'some text goes here';
        $testModel->save();

        $this->assertDatabaseHas('test_model', ['name' => 'some text goes here', 'slugs' => json_encode(['name' => 'some-text-goes-here', 'title' => ''])]);
        $this->assertDatabaseCount('test_model', 1);

        $testModel       = new $model();
        $testModel->name = 'some text goes here';
        $testModel->save();

        $this->assertDatabaseCount('test_model', 2);

        $savedModel = $testModel::all();
        $this->assertEquals($savedModel[0]->slugs, $savedModel[1]->slugs);  // Duplicated slugs are allowed
    }

    public function testItGeneratesUniqueSlugsWithMaximumLength()
    {
        $model = new class extends TestModel {
            protected $sluggables = ['name'];

            protected function getSlugConfig(): SlugOptions
            {
                return SlugOptions::make()
                    ->avoidDuplicates()
                    ->slugShouldBeNoLongerThan(10)
                    ->saveSlugsTo('slugs')
                ;
            }
        };

        $testModel       = new $model();
        $testModel->name = $name = $this->faker->sentence(5);
        $testModel->save();

        $generatedSlug = $testModel->slugs['name'];

        $this->assertLessThanOrEqual(10, strlen($generatedSlug));
        $this->assertDatabaseHas('test_model', ['name' => $testModel->name, 'slugs' => json_encode(['name' => $generatedSlug])]);

        for ($i = 1; $i < 10; ++$i) {
            $testModel       = new $model();
            $testModel->name = $name;
            $testModel->save();

            $this->assertLessThanOrEqual(10, strlen($testModel->slugs['name']));
            $this->assertMatchesRegularExpression('/-' . $i . '$/', $testModel->slugs['name']);
        }

        $this->assertDatabaseCount('test_model', 10);
    }
}
