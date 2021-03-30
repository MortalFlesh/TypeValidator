TypeValidator
=============

[![Latest Stable Version](https://img.shields.io/packagist/v/mf/type-validator.svg)](https://packagist.org/packages/mf/type-validator)
[![Tests and linting](https://github.com/MortalFlesh/TypeValidator/actions/workflows/tests.yaml/badge.svg)](https://github.com/MortalFlesh/TypeValidator/actions/workflows/tests.yaml)
[![Coverage Status](https://coveralls.io/repos/github/MortalFlesh/TypeValidator/badge.svg?branch=master)](https://coveralls.io/github/MortalFlesh/TypeValidator?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/mf/type-validator.svg)](https://packagist.org/packages/mf/type-validator)
[![License](https://img.shields.io/packagist/l/mf/type-validator.svg)](https://packagist.org/packages/mf/type-validator)

TypeValidator for asserting types of values

## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## <a name="requirements"></a>Requirements
- PHP 7.4


## <a name="installation"></a>Installation:
```
composer require mf/type-validator
```


## <a name="usage"></a>Usage
```php
$validator = new TypeValidator(
    TypeValidator::TYPE_STRING,
    TypeValidator::TYPE_INT,
    [TypeValidator::TYPE_STRING],
    [TypeValidator::INT]
);

$validator->assertKeyType('string - value');
$validator->assertValueType(1);

$validator->assertValueType('invalid value type');  // throws InvalidArgumentException
```

### With Custom Exception
```php
$validator = new TypeValidator(
    TypeValidator::TYPE_STRING,
    TypeValidator::TYPE_INT,
    [TypeValidator::TYPE_STRING],
    [TypeValidator::INT],
    App\MyCustomException::class
);

$validator->assertKeyType('string - value');
$validator->assertValueType(1);

$validator->assertValueType('invalid value type');  // throws App\MyCustomException
```
