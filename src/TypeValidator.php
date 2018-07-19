<?php declare(strict_types=1);

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

    private const TYPES = [
        self::TYPE_STRING,
        self::TYPE_INT,
        self::TYPE_FLOAT,
        self::TYPE_BOOL,
        self::TYPE_ARRAY,
        self::TYPE_OBJECT,
        self::TYPE_INSTANCE_OF,
    ];

    private const KEY = 'key';
    private const VALUE = 'value';

    /** @var string */
    private $KValue;

    /** @var string */
    private $TValue;

    /** @var array */
    private $allowedKValues;

    /** @var array */
    private $allowedTValues;

    public function __construct(string $KValue, string $TValue, array $allowedKValues, array $allowedTValues)
    {
        $KValue = $this->normalizeType($KValue);
        $TValue = $this->normalizeType($TValue);

        $allowedKValues = $this->normalizeTypes($allowedKValues);
        $allowedTValues = $this->normalizeTypes($allowedTValues);

        $this->assertValidType($KValue, self::KEY, $allowedKValues);
        $this->assertValidType($TValue, self::VALUE, $allowedTValues);

        $this->KValue = $KValue;
        $this->TValue = $TValue;
        $this->allowedKValues = $allowedKValues;
        $this->allowedTValues = $allowedTValues;
    }

    public function changeKeyType(string $KValue): void
    {
        $KValue = $this->normalizeType($KValue);
        $this->assertValidType($KValue, self::KEY, $this->allowedKValues);

        $this->KValue = $KValue;
    }

    public function changeValueType(string $TValue): void
    {
        $TValue = $this->normalizeType($TValue);
        $this->assertValidType($TValue, self::VALUE, $this->allowedTValues);

        $this->TValue = $TValue;
    }

    private function normalizeTypes(array $types): array
    {
        return array_map(function ($type) {
            return $this->normalizeType($type);
        }, $types);
    }

    private function normalizeType(string $type): string
    {
        return !$this->isInstanceOfType($type) && !in_array($type, self::TYPES, true)
            ? self::TYPE_INSTANCE_OF . $type
            : $type;
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

    private function isInstanceOfType(string $TValue): bool
    {
        return (mb_substr($TValue, 0, (int) mb_strlen(self::TYPE_INSTANCE_OF)) === self::TYPE_INSTANCE_OF);
    }

    private function assertValidInstanceOf(string $TValue): void
    {
        $class = $this->parseClass($TValue);

        if (!(class_exists($class) || interface_exists($class))) {
            throw new \InvalidArgumentException(sprintf('Instance of has invalid class (%s)', $class));
        }
    }

    private function parseClass(string $type): string
    {
        return mb_substr($type, (int) mb_strlen(self::TYPE_INSTANCE_OF));
    }

    public function getKeyType(): string
    {
        return $this->stripInstanceOfPrefix($this->KValue);
    }

    private function stripInstanceOfPrefix(string $type): string
    {
        return str_replace(self::TYPE_INSTANCE_OF, '', $type);
    }

    public function getValueType(): string
    {
        return $this->stripInstanceOfPrefix($this->TValue);
    }

    /**
     * @param <K> $key
     */
    public function assertKeyType($key): void
    {
        $this->assertType($key, $this->KValue, self::KEY);
    }

    private function assertType($givenType, string $type, string $typeTitle): void
    {
        if ($this->isInstanceOfType($type)) {
            $this->assertInstanceOf($typeTitle, $givenType);
        } elseif ($type === self::TYPE_STRING && !is_string($givenType)) {
            $this->invalidTypeError($typeTitle, self::TYPE_STRING, $givenType);
        } elseif ($type === self::TYPE_INT && !is_int($givenType)) {
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
        $class = $this->parseClass($this->TValue);

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
        }

        return sprintf('%s', $givenType);
    }

    /**
     * @param <V> $value
     */
    public function assertValueType($value): void
    {
        $this->assertType($value, $this->TValue, self::VALUE);
    }
}
