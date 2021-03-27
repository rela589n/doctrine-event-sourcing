<?php

declare(strict_types=1);


namespace Rela589n\DoctrineEventSourcing\Serializer\Composed;

use LogicException;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateDeserializer;

abstract class ComposedDeserializer
{
    public function __invoke(DeserializationContext $context): mixed
    {
        foreach ($this->deserializers() as $deserialize) {
            if ($deserialize->isPossible($context)) {
                return $deserialize($context);
            }
        }

        throw new LogicException("No deserializer matches context for '{$context->getName()}' property");
    }

    /** @return iterable<SeparateDeserializer> */
    abstract protected function deserializers(): iterable;
}
