<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Context;

final class DeserializationContext
{
    public function __construct(
        private string $fieldName,
        private string $type,
        private mixed $serialized,
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getSerialized(): mixed
    {
        return $this->serialized;
    }
}
