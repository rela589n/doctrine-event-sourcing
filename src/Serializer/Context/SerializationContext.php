<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Context;

final class SerializationContext
{
    public function __construct(private string $name, private mixed $value, private array $attributes) { }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
