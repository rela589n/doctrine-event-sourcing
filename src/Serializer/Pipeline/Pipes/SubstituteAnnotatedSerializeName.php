<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes;

use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\SerializationContextPipe;

#[Immutable]
final class SubstituteAnnotatedSerializeName implements SerializationContextPipe
{
    public function __construct(private array $namesMeta) { }

    public function __invoke(SerializationContext $context): SerializationContext
    {
        if (empty($this->namesMeta[$context->getFieldName()])) {
            return $context;
        }

        return $context->withName($this->namesMeta[$context->getFieldName()]);
    }
}
