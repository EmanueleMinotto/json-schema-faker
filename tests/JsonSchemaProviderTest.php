<?php

declare(strict_types=1);

namespace EmanueleMinotto\JsonSchemaFaker;

use Faker\Generator;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \EmanueleMinotto\JsonSchemaFaker\JsonSchemaProvider
 */
class JsonSchemaProviderTest extends TestCase
{
    /**
     * @var Generator
     */
    private $faker;

    protected function setUp(): void
    {
        $this->faker = new Generator();
        $this->faker->addProvider(new JsonSchemaProvider());
    }

    /**
     * @dataProvider jsonDataProvider
     */
    public function testJsonGeneration(string $json)
    {
        $schema = Schema::fromJsonString($json);
        $data = $this->faker->jsonSchemaContent($json);

        $validator = new Validator();
        $result = $validator->schemaValidation($data, $schema);

        $this->assertTrue($result->isValid());
    }

    public static function jsonDataProvider()
    {
        $paths = glob(__DIR__.'/schema/*.json');

        foreach ($paths as $path) {
            yield [$path => file_get_contents($path)];
        }
    }

    public function testFakeEnumEntriesArePickedFromDefinedEnumSet(): void
    {
        $json = file_get_contents(__DIR__.'/schema/enum.json');
        $data = $this->faker->jsonSchemaContent($json);
        $schema = json_decode($json);

        $definedEnumSet = $schema->properties->enumProperty->enum;
        $fakeEnumEntry = $data->enumProperty;

        $this->assertContains($fakeEnumEntry, $definedEnumSet);
    }
}
