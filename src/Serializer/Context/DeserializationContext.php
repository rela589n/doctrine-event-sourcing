<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Context;

final class DeserializationContext
{
    public function __construct(private string $name, private string $type, private mixed $serialized) { }

    public function getName(): string
    {
        return $this->name;
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
