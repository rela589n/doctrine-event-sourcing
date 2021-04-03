<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Database\GetDatabasePlatform;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Database\GetDatabasePlatform;

#[Immutable]
final class Impl implements GetDatabasePlatform
{
    public function __construct(private EntityManagerInterface $manager) { }

    public function __invoke(): AbstractPlatform
    {
        return $this->manager
            ->getConnection()
            ->getDatabasePlatform();
    }
}
