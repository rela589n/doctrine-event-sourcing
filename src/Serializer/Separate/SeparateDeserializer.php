<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate;

use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;

interface SeparateDeserializer
{
    public function isPossible(DeserializationContext $context): bool;

    public function __invoke(DeserializationContext $context): mixed;
}
