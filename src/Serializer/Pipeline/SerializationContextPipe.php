<?php

declare(strict_types=1);


namespace Rela589n\DoctrineEventSourcing\Serializer\Pipeline;

use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;

interface SerializationContextPipe
{
    public function __invoke(SerializationContext $context): SerializationContext;
}
