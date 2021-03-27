<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Composed;

use LogicException;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;

abstract class ComposedSerializer
{
    public function __invoke(SerializationContext $context): mixed
    {
        foreach ($this->serializers() as $serialize) {
            if ($serialize->isPossible($context)) {
                return $serialize($context);
            }
        }

        throw new LogicException("No serializer matches context for '{$context->getName()}' property");
    }

    /** @return iterable<SeparateSerializer> */
    abstract protected function serializers(): iterable;
}
