<?php

namespace MF\Validator\Tests;

use MF\Validator\Tests\Fixtures\DifferentEntity;
use MF\Validator\Tests\Fixtures\EntityInterface;
use MF\Validator\Tests\Fixtures\SimpleEntity;
use MF\Validator\TypeValidator;

class TypeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $keyType
     * @param string $valueType
     * @param array $allowedKeyTypes
     * @param array $allowedValueTypes
     *
     * @dataProvider invalidCreationParamsProvider
     */
    public function testShouldThrowExceptionWhenBadTypeValidatorIsCreated(
        $keyType,
        $valueType,
        array $allowedKeyTypes,
        array $allowedValueTypes
    ) {
        $this->setExpectedException(\InvalidArgumentException::class);

        new TypeValidator($keyType, $valueType, $allowedKeyTypes, $allowedValueTypes);
    }

    public function invalidCreationParamsProvider()
    {
        return [
            'not allowed type' => [
                'keyType' => 'string',
                'valueType' => 'int',
                'allowedKeyTypes' => [],
                'allowedValueTypes' => ['string'],
            ],
            'empty type given' => [
                'keyType' => null,
                'valueType' => 'int',
                'allowedKeyTypes' => ['string'],
                'allowedValueTypes' => ['string', 'int'],
            ],
            'not allowed key type' => [
                'keyType' => 'string',
                'valueType' => 'string',
                'allowedKeyTypes' => ['int'],
                'allowedValueTypes' => ['string', 'int'],
            ],
            'empty instance' => [
                'keyType' => 'string',
                'valueType' => TypeValidator::TYPE_INSTANCE_OF,
                'allowedKeyTypes' => ['int'],
                'allowedValueTypes' => ['string', 'int', TypeValidator::TYPE_INSTANCE_OF],
            ],
            'instance of bad class' => [
                'keyType' => 'string',
                'valueType' => TypeValidator::TYPE_INSTANCE_OF . 'badClass',
                'allowedKeyTypes' => ['int'],
                'allowedValueTypes' => ['string', 'int', TypeValidator::TYPE_INSTANCE_OF],
            ],
            'instance of bad class in key' => [
                'keyType' => TypeValidator::TYPE_INSTANCE_OF . 'badClass',
                'valueType' => 'string',
                'allowedKeyTypes' => ['int', TypeValidator::TYPE_INSTANCE_OF],
                'allowedValueTypes' => ['string', 'int'],
            ],
            'bad class' => [
                'keyType' => 'badClass',
                'valueType' => 'string',
                'allowedKeyTypes' => ['int', TypeValidator::TYPE_INSTANCE_OF],
                'allowedValueTypes' => ['string', 'int'],
            ],
        ];
    }

    /**
     * @param string $keyType
     * @param string $valueType
     * @param array $allowedKeyTypes
     * @param array $allowedValueTypes
     * @param string $expectedKeyType
     * @param string $expectedValueType
     *
     * @dataProvider creationParamsProvider
     */
    public function testShouldCreateTypeValidator(
        $keyType,
        $valueType,
        array $allowedKeyTypes,
        array $allowedValueTypes,
        $expectedKeyType,
        $expectedValueType
    ) {
        $typeValidator = new TypeValidator($keyType, $valueType, $allowedKeyTypes, $allowedValueTypes);

        $this->assertEquals($expectedKeyType, $typeValidator->getKeyType());
        $this->assertEquals($expectedValueType, $typeValidator->getValueType());
    }

    public function creationParamsProvider()
    {
        return [
            [
                'keyType' => 'string',
                'valueType' => 'string',
                'allowedKeyTypes' => ['string'],
                'allowedValueTypes' => ['string', 'int'],
                'expectedKeyType' => 'string',
                'expectedValueType' => 'string',
            ],
            [
                'keyType' => 'int',
                'valueType' => 'instance_of_' . SimpleEntity::class,
                'allowedKeyTypes' => ['string', 'float', 'int'],
                'allowedValueTypes' => ['string', 'bool', 'instance_of_'],
                'expectedKeyType' => 'int',
                'expectedValueType' => SimpleEntity::class,
            ],
            [
                'keyType' => 'int',
                'valueType' => SimpleEntity::class,
                'allowedKeyTypes' => ['string', 'float', 'int'],
                'allowedValueTypes' => ['string', 'bool', 'instance_of_'],
                'expectedKeyType' => 'int',
                'expectedValueType' => SimpleEntity::class,
            ],
        ];
    }

    /**
     * @param string $type
     * @param mixed $key
     * @param mixed $value
     *
     * @dataProvider validKeyValuesProvider
     */
    public function testShouldAssertKeyValueType($type, $key, $value)
    {
        $validator = $this->createValidator($type);

        $validator->assertKeyType($key);
        $validator->assertValueType($value);
    }

    /**
     * @param string $type
     * @return TypeValidator
     */
    private function createValidator($type)
    {
        return new TypeValidator($type, $type, [$type], [$type]);
    }

    public function validKeyValuesProvider()
    {
        return [
            [
                'type' => TypeValidator::TYPE_STRING,
                'key' => 'string',
                'value' => 'string',
            ],
            [
                'type' => TypeValidator::TYPE_INT,
                'key' => 1,
                'value' => 2,
            ],
            [
                'type' => TypeValidator::TYPE_FLOAT,
                'key' => 1.2,
                'value' => 2.3,
            ],
            [
                'type' => TypeValidator::TYPE_BOOL,
                'key' => true,
                'value' => false,
            ],
            [
                'type' => TypeValidator::TYPE_ARRAY,
                'key' => [],
                'value' => [1, 2, 3],
            ],
            [
                'type' => TypeValidator::TYPE_OBJECT,
                'key' => new \stdClass(),
                'value' => new \stdClass(),
            ],
            [
                'type' => TypeValidator::TYPE_INSTANCE_OF . SimpleEntity::class,
                'key' => new SimpleEntity(1),
                'value' => new SimpleEntity(2),
            ],
            [
                'type' => TypeValidator::TYPE_INSTANCE_OF . EntityInterface::class,
                'key' => new SimpleEntity(1),
                'value' => new SimpleEntity(2),
            ],
            [
                'type' => EntityInterface::class,
                'key' => new SimpleEntity(1),
                'value' => new SimpleEntity(2),
            ],
        ];
    }

    /**
     * @param string $type
     * @param mixed $key
     *
     * @dataProvider invalidTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionWhenAssertingInvalidKeyTypes($type, $key)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $validator = $this->createValidator($type);
        $validator->assertKeyType($key);
    }

    public function invalidTypesProvider()
    {
        return [
            'string|int' => [
                'type' => TypeValidator::TYPE_STRING,
                'invalid' => 1,
            ],
            'string|null' => [
                'type' => TypeValidator::TYPE_STRING,
                'invalid' => null,
            ],
            'int|string' => [
                'type' => TypeValidator::TYPE_INT,
                'invalid' => '',
            ],
            'int|bool' => [
                'type' => TypeValidator::TYPE_INT,
                'invalid' => true,
            ],
            'float|int' => [
                'type' => TypeValidator::TYPE_FLOAT,
                'invalid' => 2,
            ],
            'float|bool' => [
                'type' => TypeValidator::TYPE_FLOAT,
                'invalid' => true,
            ],
            'bool|int' => [
                'type' => TypeValidator::TYPE_BOOL,
                'invalid' => 1,
            ],
            'array|null' => [
                'type' => TypeValidator::TYPE_ARRAY,
                'invalid' => null,
            ],
            'array|string' => [
                'type' => TypeValidator::TYPE_ARRAY,
                'invalid' => '',
            ],
            'object|array' => [
                'type' => TypeValidator::TYPE_OBJECT,
                'invalid' => [],
            ],
            'instance_of_map|instance_of_list' => [
                'type' => EntityInterface::class,
                'invalid' => new DifferentEntity(1),
            ],
        ];
    }

    /**
     * @param string $type
     * @param mixed $value
     *
     * @dataProvider invalidTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionWhenAssertingInvalidValueTypes($type, $value)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $validator = $this->createValidator($type);
        $validator->assertValueType($value);
    }

    /**
     * @param string $type
     * @param mixed $value
     *
     * @dataProvider invalidValuesProvider
     */
    public function testShouldValidateClassTypeWithInvalidValue($type, $value)
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Invalid value type argument "MF\Validator\Tests\Fixtures\DifferentEntity"<object> given - ' .
            '<instance of (MF\Validator\Tests\Fixtures\SimpleEntity)> expected'
        );

        $validator = new TypeValidator(
            TypeValidator::TYPE_STRING,
            $type,
            [TypeValidator::TYPE_STRING],
            [TypeValidator::TYPE_INSTANCE_OF]
        );

        $validator->assertValueType($value);
    }

    public function invalidValuesProvider()
    {
        return [
            'map/list' => [SimpleEntity::class, new DifferentEntity(1)],
        ];
    }
}
