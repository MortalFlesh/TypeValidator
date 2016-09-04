TypeValidator
=============

[![Build Status](https://travis-ci.org/MortalFlesh/TypeValidator.svg?branch=master)](https://travis-ci.org/MortalFlesh/TypeValidator)
[![Coverage Status](https://coveralls.io/repos/github/MortalFlesh/TypeValidator/badge.svg?branch=master)](https://coveralls.io/github/MortalFlesh/TypeValidator?branch=master)

TypeValidator for asserting types of values

## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## <a name="requirements"></a>Requirements
- PHP 5.5


## <a name="installation"></a>Installation:
```
//composer.json
{
    "require": {
        "mf/type-validator": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "https://github.com/MortalFlesh/TypeValidator.git"
        }
    ]
}

// console
composer install
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
