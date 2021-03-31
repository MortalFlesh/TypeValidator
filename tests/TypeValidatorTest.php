<?php declare(strict_types=1);

namespace MF\Validator;

use MF\Validator\Fixtures\CustomException;
use MF\Validator\Fixtures\DifferentEntity;
use MF\Validator\Fixtures\EntityInterface;
use MF\Validator\Fixtures\SimpleEntity;
use PHPUnit\Framework\TestCase;

class TypeValidatorTest extends TestCase
{
    /** @dataProvider invalidCreationParamsProvider */
    public function testShouldThrowExceptionWhenBadTypeValidatorIsCreated(
        ?string $keyType,
        ?string $valueType,
        array $allowedKeyTypes,
        array $allowedValueTypes
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        new TypeValidator((string) $keyType, (string) $valueType, $allowedKeyTypes, $allowedValueTypes);
    }

    public function invalidCreationParamsProvider(): array
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

    /** @dataProvider creationParamsProvider */
    public function testShouldCreateTypeValidator(
        string $keyType,
        string $valueType,
        array $allowedKeyTypes,
        array $allowedValueTypes,
        string $expectedKeyType,
        string $expectedValueType
    ): void {
        $typeValidator = new TypeValidator($keyType, $valueType, $allowedKeyTypes, $allowedValueTypes);

        $this->assertEquals($expectedKeyType, $typeValidator->getKeyType());
        $this->assertEquals($expectedValueType, $typeValidator->getValueType());
    }

    public function creationParamsProvider(): array
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
            [
                'keyType' => 'int',
                'valueType' => 'callable',
                'allowedKeyTypes' => ['string', 'float', 'int'],
                'allowedValueTypes' => ['callable'],
                'expectedKeyType' => 'int',
                'expectedValueType' => 'callable',
            ],
        ];
    }

    /** @dataProvider validKeyValuesProvider */
    public function testShouldAssertKeyValueType(string $type, mixed $key, mixed $value): void
    {
        $validator = $this->createValidator($type);

        $validator->assertKeyType($key);
        $validator->assertValueType($value);

        $this->assertTrue(true);
    }

    private function createValidator(string $type): TypeValidator
    {
        return new TypeValidator($type, $type, [$type], [$type]);
    }

    public function validKeyValuesProvider(): array
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
            [
                'type' => 'callable',
                'key' => function () {
                    return 'key';
                },
                'value' => 'trim',
            ],
            [
                'type' => 'callable',
                'key' => [$this, 'assertSame'],
                'value' => [SimpleEntity::class, 'create'],
            ],
            [
                'type' => 'array',
                'key' => [SimpleEntity::class, 'create'],
                'value' => [$this, 'assertSame'],
            ],
            [
                'type' => 'string',
                'key' => 'trim',
                'value' => 'strpos',
            ],
        ];
    }

    /** @dataProvider validKeyValuesProvider */
    public function testShouldAllowAnyKeyValueTypeOfMixedType(string $type, mixed $key, mixed $value): void
    {
        $validator = $this->createValidator('mixed');

        $validator->assertKeyType($key);
        $validator->assertValueType($value);

        $this->assertTrue(true);
    }

    /** @dataProvider validKeyValuesProvider */
    public function testShouldAllowAnyKeyValueTypeOfAnyType(string $type, mixed $key, mixed $value): void
    {
        $validator = $this->createValidator('any');

        $validator->assertKeyType($key);
        $validator->assertValueType($value);

        $this->assertTrue(true);
    }

    /** @dataProvider invalidTypesProvider */
    public function testShouldThrowInvalidArgumentExceptionWhenAssertingInvalidKeyTypes(string $type, mixed $key): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $validator = $this->createValidator($type);
        $validator->assertKeyType($key);
    }

    public function invalidTypesProvider(): array
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
            'string|bool' => [
                'type' => TypeValidator::TYPE_STRING,
                'invalid' => false,
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
            'not defined function in callable pair' => [
                'type' => TypeValidator::TYPE_CALLABLE,
                'invalid' => [SimpleEntity::class, 'notDefinedMethod'],
            ],
            'array|callable' => [
                'type' => TypeValidator::TYPE_ARRAY,
                'invalid' => function () {
                    return 'foo';
                },
            ],
            'string|callable' => [
                'type' => TypeValidator::TYPE_STRING,
                'invalid' => function () {
                    return 'foo';
                },
            ],
        ];
    }

    /** @dataProvider invalidTypesProvider */
    public function testShouldThrowInvalidArgumentExceptionWhenAssertingInvalidValueTypes(
        string $type,
        mixed $value
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        $validator = $this->createValidator($type);
        $validator->assertValueType($value);
    }

    /** @dataProvider invalidValuesProvider */
    public function testShouldValidateClassTypeWithInvalidValue(string $type, mixed $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid value type argument "MF\Validator\Fixtures\DifferentEntity"<object> given - ' .
            '<instance of (MF\Validator\Fixtures\SimpleEntity)> expected'
        );

        $validator = new TypeValidator(
            TypeValidator::TYPE_STRING,
            $type,
            [TypeValidator::TYPE_STRING],
            [TypeValidator::TYPE_INSTANCE_OF]
        );

        $validator->assertValueType($value);
    }

    public function invalidValuesProvider(): array
    {
        return [
            'map/list' => [SimpleEntity::class, new DifferentEntity(1)],
        ];
    }

    public function testShouldThrowExceptionOnInvalidClassCreation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Instance of has invalid class (badClass)');

        new TypeValidator(
            TypeValidator::TYPE_STRING,
            'badClass',
            [TypeValidator::TYPE_STRING],
            [TypeValidator::TYPE_INSTANCE_OF]
        );
    }

    public function testShouldChangeAssertedTypeOfValidator(): void
    {
        $string = 'string';
        $int = 1;

        $validator = new TypeValidator('string', 'string', ['string', 'int'], ['string', 'int']);

        $validator->assertKeyType($string);
        $validator->assertValueType($string);

        $validator->changeValueType('int');
        $validator->assertValueType($int);

        $validator->changeKeyType('int');
        $validator->assertKeyType($int);

        $this->assertTrue(true);
    }

    public function testShouldThrowCustomExceptionOnCreation(): void
    {
        $this->expectException(CustomException::class);
        $this->expectExceptionMessage('Not allowed key type given - <string>, expected one of []');

        new TypeValidator('string', 'string', [], [], CustomException::class);
    }

    public function testShouldThrowCustomExceptionOnAssertion(): void
    {
        $validator = new TypeValidator('string', 'string', ['string'], ['string'], CustomException::class);

        $this->expectException(CustomException::class);
        $this->expectExceptionMessage('Invalid key type argument "1"<integer> given - <string> expected');

        $validator->assertKeyType(1);
    }

    /** @dataProvider provideInvalidExceptionClass */
    public function testShouldNotCreateTypeValidatorWithInvalidCustomException(
        string $invalidException,
        string $expectedMessage
    ): void {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage($expectedMessage);

        new TypeValidator('string', 'string', ['string'], ['string'], $invalidException);
    }

    public function provideInvalidExceptionClass(): array
    {
        return [
            // invalidException, expectedMessage
            'not a class' => ['Just some string', 'Given exception class "Just some string" does not exists.'],
            'not implements Throwable interface' => [
                SimpleEntity::class,
                'Given exception class must implement Throwable interface',
            ],
        ];
    }
}
