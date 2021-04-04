<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Context;

use JetBrains\PhpStorm\Immutable;

#[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
class SerializationContext
{
    protected string $fieldName;
    protected mixed $value;
    protected array $attributes;
    protected ?string $name = null;

    public static function make(): static
    {
        return new static();
    }

    public function withFieldName(string $fieldName): static
    {
        $static = clone $this;
        $static->fieldName = $fieldName;
        return $static;
    }

    public function withValue(mixed $value): static
    {
        $static = clone $this;
        $static->value = $value;
        return $static;
    }

    public function withAttributes(array $attributes): static
    {
        $static = clone $this;
        $static->attributes = $attributes;
        return $static;
    }

    public function withName(string $name): static
    {
        $static = clone $this;
        $static->name = $name;
        return $static;
    }

    public function getName(): string
    {
        return $this->name
            ?? $this->fieldName;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
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
