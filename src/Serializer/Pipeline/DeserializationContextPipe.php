<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Pipeline;

use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;

interface DeserializationContextPipe
{
    public function __invoke(DeserializationContext $context): DeserializationContext;
}
