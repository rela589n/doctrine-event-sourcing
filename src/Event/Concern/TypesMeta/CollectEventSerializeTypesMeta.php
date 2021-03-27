<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event\Concern\TypesMeta;

use ReflectionProperty;

interface CollectEventSerializeMeta
{
    public function __invoke(ReflectionProperty...$properties): array;
}
