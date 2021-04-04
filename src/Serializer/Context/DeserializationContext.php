<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Context;

use JetBrains\PhpStorm\Immutable;

#[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
class DeserializationContext
{
    protected string $fieldName;
    protected string $type;
    protected array $serialized;
    protected ?string $name = null;

    private function __construct()
    {
    }

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

    public function withType(string $type): static
    {
        $static = clone $this;
        $static->type = $type;
        return $static;
    }

    public function withSerialized(array $serialized): static
    {
        $static = clone $this;
        $static->serialized = $serialized;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getSerialized(): array
    {
        return $this->serialized;
    }
}
