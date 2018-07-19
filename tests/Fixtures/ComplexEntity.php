<?php declare(strict_types=1);

namespace MF\Validator\Fixtures;

class ComplexEntity implements EntityInterface
{
    /** @var SimpleEntity */
    private $simpleEntity;

    public function __construct(SimpleEntity $simpleEntity)
    {
        $this->simpleEntity = $simpleEntity;
    }

    public function getSimpleEntity(): SimpleEntity
    {
        return $this->simpleEntity;
    }
}
