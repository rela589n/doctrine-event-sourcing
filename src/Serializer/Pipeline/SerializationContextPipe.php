<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Pipeline;

use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;

#[Immutable]
interface SerializationContextPipe
{
    public function __invoke(SerializationContext $context): SerializationContext;
}
