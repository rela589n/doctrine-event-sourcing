<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer\Factory;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Event\Annotations\SerializeAs;
use Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer\Factory;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes\SubstituteAnnotatedDeserializeName;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\DeserializeCastable;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded\DeserializeEmbedded;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\DeserializeEntity;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\DeserializeNoop;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateDeserializer;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed\DeserializeTyped;

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

    /** @return iterable<DeserializationContext> */
    protected function pipes(DeserializationContext $context): Generator
    {
        yield new SubstituteAnnotatedDeserializeName($this->namesMeta);
    }

    /** @return iterable<SeparateDeserializer> */
    protected function deserializers(DeserializationContext $context): Generator
    {
        yield DeserializeTyped::from($this->manager, $this->typesMeta);
        yield DeserializeEntity::from($this->manager);
        yield DeserializeCastable::from($this->entity, $this->castArgumentsMap);
        yield DeserializeEmbedded::from($this->manager, $this->entity);
        yield DeserializeNoop::instance();
    }
}
