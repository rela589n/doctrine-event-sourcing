<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Pipeline;

use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;

#[Immutable]
interface DeserializationContextPipe
{
    public function __invoke(DeserializationContext $context): DeserializationContext;
}
