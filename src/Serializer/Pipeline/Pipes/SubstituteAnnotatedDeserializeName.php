<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes;

use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\DeserializationContextPipe;

final class SubstituteAnnotatedDeserializeName implements DeserializationContextPipe
{
    public function __construct(private array $namesMeta) { }

    public function __invoke(DeserializationContext $context): DeserializationContext
    {
        if (empty($this->namesMeta[$context->getFieldName()])) {
            return $context;
        }

        return new DeserializationContext(
            $context->getFieldName(),
            $context->getType(),
            $context->getSerialized(),
            $this->namesMeta[$context->getFieldName()],
        );
    }
}
