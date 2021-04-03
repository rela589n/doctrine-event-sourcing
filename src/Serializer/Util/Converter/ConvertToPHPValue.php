<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Converter;

use Doctrine\DBAL\Types\Type;

interface ConvertToPHPValue
{
    public function __invoke(string|Type $type, mixed $value): mixed;
}
