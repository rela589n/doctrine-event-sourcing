<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Database;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;

final class GetDatabasePlatform
{
    public function __construct(private EntityManagerInterface $manager) { }

    public function __invoke(): AbstractPlatform
    {
        return $this->manager
            ->getConnection()
            ->getDatabasePlatform();
    }
}
