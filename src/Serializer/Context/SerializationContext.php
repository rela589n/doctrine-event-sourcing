<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Context;

final class SerializationContext
{
    public function __construct(
        private string $fieldName,
        private mixed $value,
        private array $attributes,
        private ?string $name = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name ?? $this->fieldName;
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
