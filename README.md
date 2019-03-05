# JSON Schema Faker

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Utility based on [fzaninotto/Faker](https://github.com/fzaninotto/Faker) to generate fake JSON starting from a JSON Schema.

## Structure

If any of the following are applicable to your project, then the directory structure should follow industry best practices by being named the following.

```
bin/        
config/
src/
tests/
vendor/
```


## Install

Via Composer

``` bash
$ composer require emanueleminotto/json-schema-faker
```

## Usage

```php
$faker = Faker\Factory::create();
$faker->addProvider(new EmanueleMinotto\JsonSchemaFaker\JsonSchemaProvider());

$schema = '{
  "type": "array",
  "items": [
    {"type": "integer"},
    {"type": "string"}
  ]
}';
$data = $faker->jsonSchemaContent($schema);
// $data = $faker->jsonSchema(json_decode($schema, true));

var_dump($data);
/*
array(2) {
  [0]=>
  int(2336562738116576768)
  [1]=>
  string(62) "Officiis qui officiis quasi. Sed et dolorem omnis repellendus."
}
*/
```

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email minottoemanuele@gmail.com instead of using the issue tracker.

## Credits

- [Emanuele Minotto][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/emanueleminotto/json-schema-faker.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/EmanueleMinotto/json-schema-faker/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/EmanueleMinotto/json-schema-faker.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/EmanueleMinotto/json-schema-faker.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/emanueleminotto/json-schema-faker.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/emanueleminotto/json-schema-faker
[link-travis]: https://travis-ci.org/EmanueleMinotto/json-schema-faker
[link-scrutinizer]: https://scrutinizer-ci.com/g/EmanueleMinotto/json-schema-faker/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/EmanueleMinotto/json-schema-faker
[link-downloads]: https://packagist.org/packages/emanueleminotto/json-schema-faker
[link-author]: https://github.com/EmanueleMinotto
[link-contributors]: ../../contributors
