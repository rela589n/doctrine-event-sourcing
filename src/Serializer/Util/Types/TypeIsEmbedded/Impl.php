<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Types\TypeIsEmbedded;

use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Types\TypeIsEmbedded;

#[Immutable]
final class Impl implements TypeIsEmbedded
{
    public function __construct(private EntityManagerInterface $manager) { }

    public function __invoke(string $className): bool
    {
        return ($this->manager->getMetadataFactory()
                              ->getLoadedMetadata()[$className] ?? null)
                ?->isEmbeddedClass ?? false;
    }
}
