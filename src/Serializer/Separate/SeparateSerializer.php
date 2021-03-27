<?php

declare(strict_types=1);


namespace Rela589n\DoctrineEventSourcing\Serializer\Separate;

use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;

interface SeparateSerializer
{
    public function isPossible(SerializationContext $context): bool;

    public function __invoke(SerializationContext $context): mixed;
}
