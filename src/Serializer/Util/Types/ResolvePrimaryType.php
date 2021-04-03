<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Types;

use Doctrine\DBAL\Types\Type;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;

interface ResolvePrimaryType
{
    /** @param  string|AggregateRoot  $className */
    public function __invoke(string $className): Type;
}
