<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract;

interface Castable
{
    /** @return CastsAttributes */
    public static function castUsing(array $arguments);
}
