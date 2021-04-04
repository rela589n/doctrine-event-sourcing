<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer;

use Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;

abstract class Factory
{
    final public function make(): ComposedDeserializer
    {
        return new ComposedDeserializer(
            fn($c) => $this->pipes($c),
            fn($c) => $this->deserializers($c),
        );
    }

    abstract protected function pipes(DeserializationContext $context): iterable;

    abstract protected function deserializers(DeserializationContext $context): iterable;
}
