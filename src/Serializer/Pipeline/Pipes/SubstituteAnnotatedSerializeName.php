<?php

declare(strict_types=1);


namespace Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes;

use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\SerializationContextPipe;

final class SubstituteAnnotatedSerializeName implements SerializationContextPipe
{
    public function __construct(private array $namesMeta) { }

    public function __invoke(SerializationContext $context): SerializationContext
    {
        if (empty($this->namesMeta[$context->getFieldName()])) {
            return $context;
        }

        return new SerializationContext(
            $context->getFieldName(),
            $context->getValue(),
            $context->getAttributes(),
            $this->namesMeta[$context->getFieldName()],
        );
    }
}
