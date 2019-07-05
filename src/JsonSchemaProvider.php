<?php

namespace EmanueleMinotto\JsonSchemaFaker;

use Faker\Provider\Base;
use Faker\Provider\Lorem;

class JsonSchemaProvider
{
    public static function jsonSchemaContent(string $content)
    {
        $schema = json_decode($content, true);

        return static::jsonSchema($schema);
    }

    public static function jsonSchema(array $schema)
    {
        if (!empty($schema['enum'])) {
            return array_rand(array_flip($schema['enum']));
        }

        if (!isset($schema['type'])) {
            return null;
        }

        switch ($schema['type']) {
            case 'string':
                return static::fromString($schema);
            case 'number':
                return static::fromNumber($schema);
            case 'integer':
                return static::fromInteger($schema);
            case 'boolean':
                return static::fromBoolean($schema);
            case 'array':
                return static::fromArray($schema);
            case 'object':
                return static::fromObject($schema);
            case 'null':
            default:
                return null;
        }
    }

    private static function fromString(array $schema): string
    {
        $output = '';
        if (isset($schema['pattern'])) {
            $output = Base::regexify($schema['pattern']);
        }

        $minLength = max($schema['minLength'] ?? 1, 1);
        $maxLength = min($schema['maxLength'] ?? 100, 100);

        $max = max($minLength, $maxLength);
        $output = $output ?: Lorem::text(max($max, 5));
        $output = substr($output, 0, $max);

        $contentMediaType = $schema['contentMediaType'] ?? 'text/plain';
        $contentMediaType = in_array($contentMediaType, ['text/plain', 'application/json'])
            ? $contentMediaType
            : 'text/plain';

        if ('application/json' === $contentMediaType) {
            $output = json_encode($output);
        }

        $contentEncoding = $schema['contentEncoding'] ?? 'binary';
        $contentEncoding = in_array($contentEncoding, ['binary', 'base64'])
            ? $contentEncoding
            : 'binary';

        if ('base64' === $contentEncoding) {
            $output = base64_encode($output);
        }

        return $output;
    }

    private static function fromNumber(array $schema): float
    {
        $minimum = $schema['minimum'] ?? 0;
        if (isset($schema['exclusiveMinimum'])) {
            if (true === $schema['exclusiveMinimum']) {
                $minimum += 0.001;
            } else {
                $minimum = $schema['exclusiveMinimum'];
            }
        }

        $maximum = $schema['maximum'] ?? PHP_INT_MAX;
        if (isset($schema['exclusiveMaximum'])) {
            if (true === $schema['exclusiveMaximum']) {
                $maximum -= 0.001;
            } else {
                $maximum = $schema['exclusiveMaximum'];
            }
        }

        $multipleOf = $schema['multipleOf'] ?? 1;

        $output = Base::randomFloat(null, $minimum, $maximum / max($multipleOf, 1));
        if (1 !== $multipleOf) {
            $output = $multipleOf * round($output);
        }

        return min(max($output, $minimum), $maximum);
    }

    private static function fromInteger(array $schema): int
    {
        return round(static::fromNumber($schema));
    }

    private static function fromBoolean(array $schema): bool
    {
        return 1 === mt_rand(0, 1);
    }

    private static function fromArray(array $schema): array
    {
        $minItems = max($schema['minItems'] ?? 0, 0);
        $maxItems = min($schema['maxItems'] ?? 50, 50);
        $uniqueItems = $schema['uniqueItems'] ?? false;

        $items = $schema['items'] ?? [];
        if (!isset($items[0]) && !empty($items)) {
            $items = array_fill(0, max($minItems, 1), $items);

            unset($schema['additionalItems'], $schema['contains']);
        }

        if (isset($schema['contains'])) {
            $items = array_fill(0, max($minItems, 1), $schema['contains']);
        }

        if (isset($schema['additionalItems'])) {
            $items += array_fill(count($items), max(0, $maxItems - count($items)), $schema['additionalItems']);
        }

        if (empty($items)) {
            $type = array_rand(array_flip(['string', 'number', 'integer']));
            $items = array_fill(0, mt_rand($minItems, $maxItems), [
                'type' => $type,
            ]);
        }

        $data = [];
        for ($i = 0; $i < count($items); ++$i) {
            $value = static::jsonSchema($items[$i]);

            if ($uniqueItems && in_array($value, $data, true)) {
                --$i;
                continue;
            }

            $data[] = $value;
        }

        return $data;
    }

    private static function fromObject(array $schema): \stdClass
    {
        $object = new \stdClass();

        if (!empty($schema['properties'])) {
            foreach ($schema['properties'] as $property => $subschema) {
                if (0 === mt_rand(0, 1) && !in_array($property, $schema['required'] ?? [])) {
                    continue;
                }

                $object->{$property} = !is_bool($subschema)
                    ? static::jsonSchema($subschema)
                    : static::getFromPatternProperties($property, $schema);
            }
        }

        $minProperties = max($schema['minProperties'] ?? 0, 0);

        if (0 !== $minProperties && count(get_object_vars($object)) < $minProperties) {
            do {
                $propertyName = isset($schema['propertyNames'])
                    ? static::jsonSchema($schema['propertyNames'])
                    : Lorem::word();

                $object->{$propertyName} = static::getFromPatternProperties($propertyName, $schema);
            } while (count(get_object_vars($object)) < $minProperties);
        }

        if (isset($schema['maxProperties']) && count(get_object_vars($object)) < $schema['maxProperties']) {
            do {
                $propertyName = isset($schema['propertyNames'])
                    ? static::jsonSchema($schema['propertyNames'])
                    : Lorem::word();

                $object->{$propertyName} = static::getFromPatternProperties($propertyName, $schema);
            } while (count(get_object_vars($object)) < mt_rand($minProperties, $schema['maxProperties']));
        }

        foreach ($schema['required'] ?? [] as $property) {
            if (!isset($object->{$property})) {
                $object->{$property} = static::getFromPatternProperties($property, $schema);
            }
        }

        return $object;
    }

    private static function getFromPatternProperties(string $property, array $schema)
    {
        if (isset($schema['patternProperties'])) {
            foreach ($schema['patternProperties'] as $key => $value) {
                if (preg_match('/'.$key.'/', $property)) {
                    return static::jsonSchema($schema);
                }
            }
        }

        if (isset($schema['additionalProperties']) && !is_bool($schema['additionalProperties'])) {
            return static::jsonSchema($schema['additionalProperties']);
        }

        return static::fromRandom();
    }

    private static function fromRandom()
    {
        $type = array_rand(array_flip(['string', 'number', 'integer']));

        return static::jsonSchema(['type' => $type]);
    }
}
