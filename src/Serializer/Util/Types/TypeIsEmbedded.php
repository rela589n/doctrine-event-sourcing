<?php

declare(strict_types=1);


namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Types;

use Doctrine\ORM\EntityManagerInterface;

/* @final */class TypeIsEmbedded
{
    public function __construct(private EntityManagerInterface $manager) { }

    public function __invoke(string $className): bool
    {
        return ($this->manager->getMetadataFactory()
                              ->getLoadedMetadata()[$className] ?? null)
                ?->isEmbeddedClass ?? false;
    }
}
