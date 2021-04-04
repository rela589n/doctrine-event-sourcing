<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;

final class Context
{
    private EntityManagerInterface $manager;
    private AggregateRoot $entity;
    private array $propertiesMeta;
    private array $castArgumentsMap;

    public static function make(): self
    {
        return new self();
    }

    public function withEntityManager(EntityManagerInterface $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function withEntity(AggregateRoot $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function withPropertiesMeta(array $propertiesMeta): self
    {
        $this->propertiesMeta = $propertiesMeta;

        return $this;
    }

    public function withCastArgumentsMap(array $castArgumentsMap): self
    {
        $this->castArgumentsMap = $castArgumentsMap;

        return $this;
    }

    public function getManager(): EntityManagerInterface
    {
        return $this->manager;
    }

    public function getEntity(): AggregateRoot
    {
        return $this->entity;
    }

    public function getPropertiesMeta(): array
    {
        return $this->propertiesMeta;
    }

    public function getCastArgumentsMap(): array
    {
        return $this->castArgumentsMap;
    }
}
