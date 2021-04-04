<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes;

use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\DeserializationContextPipe;

#[Immutable]
final class SubstituteAnnotatedDeserializeName implements DeserializationContextPipe
{
    public function __construct(private array $namesMeta) { }

    public function __invoke(DeserializationContext $context): DeserializationContext
    {
        if (empty($this->namesMeta[$context->getFieldName()])) {
            return $context;
        }

        return $context->withName($this->namesMeta[$context->getFieldName()]);
    }
}
