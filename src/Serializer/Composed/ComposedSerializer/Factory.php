<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer;

use Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;

abstract class Factory
{
    final public function make(): ComposedSerializer
    {
        return new ComposedSerializer(
            fn($c) => $this->pipes($c),
            fn($c) => $this->serializers($c),
        );
    }

    abstract protected function pipes(SerializationContext $context): iterable;

    abstract protected function serializers(SerializationContext $context): iterable;
}
