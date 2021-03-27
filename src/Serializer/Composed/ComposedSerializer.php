<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Composed;

use LogicException;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\SerializationContextPipe;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;

abstract class ComposedSerializer
{
    public function __invoke(SerializationContext $context): mixed
    {
        foreach ($this->pipes($context) as $pipe) {
            $context = $pipe($context);
        }

        foreach ($this->serializers($context) as $serialize) {
            if ($serialize->isPossible($context)) {
                return [
                    'name' => $context->getName(),
                    'value' => $serialize($context)
                ];
            }
        }

        throw new LogicException("No serializer matches context for '{$context->getFieldName()}' property");
    }

    /** @return iterable<SerializationContextPipe> */
    protected function pipes(SerializationContext $context): iterable
    {
        return [];
    }

    /** @return iterable<SeparateSerializer> */
    abstract protected function serializers(SerializationContext $context): iterable;
}
