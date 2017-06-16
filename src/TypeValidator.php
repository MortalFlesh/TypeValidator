<?php

namespace MF\Validator;

class TypeValidator
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOL = 'bool';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_INSTANCE_OF = 'instance_of_';

    private static $types = [
        self::TYPE_STRING,
        self::TYPE_INT,
        self::TYPE_FLOAT,
        self::TYPE_BOOL,
        self::TYPE_ARRAY,
        self::TYPE_OBJECT,
        self::TYPE_INSTANCE_OF,
    ];

    /** @var string */
    private $keyType;

    /** @var string */
    private $valueType;

    public function __construct(string $keyType, string $valueType, array $allowedKeyTypes, array $allowedValueTypes)
    {
        $keyType = $this->normalizeType($keyType);
        $valueType = $this->normalizeType($valueType);

        $allowedKeyTypes = $this->normalizeTypes($allowedKeyTypes);
        $allowedValueTypes = $this->normalizeTypes($allowedValueTypes);

        $this->assertValidType($keyType, 'key', $allowedKeyTypes);
        $this->assertValidType($valueType, 'value', $allowedValueTypes);

        $this->keyType = $keyType;
        $this->valueType = $valueType;
    }

    private function normalizeTypes(array $types): array
    {
        return array_map(function ($type) {
            return $this->normalizeType($type);
        }, $types);
    }

    private function normalizeType(string $type): string
    {
        if (!$this->isInstanceOfType($type) && !in_array($type, self::$types, true)) {
            return self::TYPE_INSTANCE_OF . $type;
        }

        return $type;
    }

    private function assertValidType(string $type, string $typeTitle, array $allowedTypes): void
    {
        if (in_array(self::TYPE_INSTANCE_OF, $allowedTypes, true) && $this->isInstanceOfType($type)) {
            $this->assertValidInstanceOf($type);
        } elseif (!in_array($type, $allowedTypes, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Not allowed %s type given - <%s>, expected one of [%s]',
                    $typeTitle,
                    $type,
                    implode(', ', $allowedTypes)
                )
            );
        }
    }

    private function isInstanceOfType(string $valueType): bool
    {
        return (substr($valueType, 0, strlen(self::TYPE_INSTANCE_OF)) === self::TYPE_INSTANCE_OF);
    }

    private function assertValidInstanceOf(string $valueType): void
    {
        $class = $this->parseClass($valueType);

        if (!(class_exists($class) || interface_exists($class))) {
            throw new \InvalidArgumentException(sprintf('Instance of has invalid class (%s)', $class));
        }
    }

    private function parseClass(string $type): string
    {
        return substr($type, strlen(self::TYPE_INSTANCE_OF));
    }

    public function getKeyType(): string
    {
        return $this->stripInstanceOfPrefix($this->keyType);
    }

    private function stripInstanceOfPrefix(string $type): string
    {
        return str_replace(self::TYPE_INSTANCE_OF, '', $type);
    }

    public function getValueType(): string
    {
        return $this->stripInstanceOfPrefix($this->valueType);
    }

    /**
     * @param <K> $key
     */
    public function assertKeyType($key): void
    {
        $this->assertType($key, $this->keyType, 'key');
    }

    private function assertType($givenType, string $type, string $typeTitle): void
    {
        if ($this->isInstanceOfType($type)) {
            $this->assertInstanceOf($typeTitle, $givenType);
        } elseif ($type === self::TYPE_STRING && !is_string($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_STRING, $givenType);
        } elseif ($type === self::TYPE_INT && !is_integer($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_INT, $givenType);
        } elseif ($type === self::TYPE_FLOAT && !is_float($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_FLOAT, $givenType);
        } elseif ($type === self::TYPE_BOOL && !($givenType === true || $givenType === false)) {
            $this->invalidTypeError($typeTitle, self::TYPE_BOOL, $givenType);
        } elseif ($type === self::TYPE_ARRAY && !is_array($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_ARRAY, $givenType);
        } elseif ($type === self::TYPE_OBJECT && !is_object($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_OBJECT, $givenType);
        }
    }

    /**
     * @param string $type
     * @param <V> $value
     */
    private function assertInstanceOf(string $type, $value): void
    {
        $class = $this->parseClass($this->valueType);

        if (!$value instanceof $class) {
            $this->invalidTypeError($type, sprintf('instance of (%s)', $class), $value);
        }
    }

    private function invalidTypeError(string $typeTitle, string $typeExpected, $givenType): void
    {
        throw new \InvalidArgumentException(
            sprintf(
                'Invalid %s type argument "%s"<%s> given - <%s> expected',
                $typeTitle,
                $this->getGivenTypeString($givenType),
                gettype($givenType),
                $typeExpected
            )
        );
    }

    private function getGivenTypeString($givenType): string
    {
        if (is_array($givenType)) {
            return 'Array';
        } elseif (is_object($givenType)) {
            return get_class($givenType);
        } elseif ($givenType === true) {
            return 'true';
        } elseif ($givenType === false) {
            return 'false';
        } else {
            return sprintf('%s', $givenType);
        }
    }

    /**
     * @param <V> $value
     */
    public function assertValueType($value): void
    {
        $this->assertType($value, $this->valueType, 'value');
    }
}
