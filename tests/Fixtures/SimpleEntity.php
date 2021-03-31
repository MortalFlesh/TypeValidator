<?php declare(strict_types=1);

namespace MF\Validator\Fixtures;

class SimpleEntity implements EntityInterface
{
    private int $id;

    public static function create(int $id): self
    {
        return new self($id);
    }

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
