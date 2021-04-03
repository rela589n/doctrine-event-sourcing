<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Database;

use Doctrine\DBAL\Platforms\AbstractPlatform;

interface GetDatabasePlatform
{
    public function __invoke(): AbstractPlatform;
}
