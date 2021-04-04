<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer\Factory;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Event\Annotations\SerializeAs;
use Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer\Factory;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes\SubstituteAnnotatedSerializeName;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\SerializeCastable;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded\SerializeEmbedded;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\SerializeEntity;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\SerializeNoop;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed\SerializeTyped;

#[Immutable]
final class Impl extends Factory
{
    /** @var Type[] */
    private array $typesMeta;
    private array $namesMeta;

    private function __construct(
        private EntityManagerInterface $manager,
        private AggregateRoot $entity,
        private array $propertiesMeta,
        private array $castArgumentsMap,
    ) {
        $this->propertiesMeta = array_map(static fn(SerializeAs $as) => $as, $this->propertiesMeta);
        $this->typesMeta = array_filter(array_map(static fn(SerializeAs $as) => $as->getType(), $this->propertiesMeta));
        $this->namesMeta = array_filter(array_map(static fn(SerializeAs $as) => $as->getName(), $this->propertiesMeta));
    }

    public static function fromContext(Context $context): self
    {
        return new self(
            $context->getManager(),
            $context->getEntity(),
            $context->getPropertiesMeta(),
            $context->getCastArgumentsMap(),
        );
    }

    protected function pipes(SerializationContext $context): Generator
    {
        yield new SubstituteAnnotatedSerializeName($this->namesMeta);
    }

    protected function serializers(SerializationContext $context): Generator
    {
        yield SerializeTyped::from($this->manager, $this->typesMeta);
        yield SerializeEntity::from($this->manager);
        yield SerializeCastable::from($this->entity, $this->castArgumentsMap);
        yield SerializeEmbedded::from($this->manager, $this->entity);
        yield SerializeNoop::instance();
    }
}
