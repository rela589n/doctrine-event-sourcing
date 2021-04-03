<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Types;

interface TypeIsEmbedded
{
    public function __invoke(string $className): bool;
}
