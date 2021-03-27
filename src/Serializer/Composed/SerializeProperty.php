<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Composed;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Event\Annotations\SerializeAs;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes\SubstituteAnnotatedSerializeName;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\SerializationContextPipe;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\SerializeCastable;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded\SerializeEmbedded;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\SerializeEntity;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\SerializeNoop;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed\SerializeTyped;

final class SerializeProperty extends ComposedSerializer
{
    /** @var Type[] */
    private array $typesMeta;
    private array $namesMeta;

    public function __construct(
        private EntityManagerInterface $manager,
        private AggregateRoot $entity,
        private array $propertiesMeta,
        private array $castArgumentsMap = [],
    ) {
        $this->propertiesMeta = array_map(static fn(SerializeAs $as) => $as, $this->propertiesMeta);
        $this->typesMeta = array_map(static fn(SerializeAs $as) => $as->getType(), $this->propertiesMeta);
        $this->namesMeta = array_filter(array_map(static fn(SerializeAs $as) => $as->getName(), $this->propertiesMeta));
    }

    /** @return iterable<SerializationContextPipe> */
    protected function pipes(SerializationContext $context): iterable
    {
        yield new SubstituteAnnotatedSerializeName($this->namesMeta);
    }

    /** @return iterable<SeparateSerializer> */
    protected function serializers(SerializationContext $context): iterable
    {
        yield SerializeTyped::from($this->manager, $this->typesMeta);
        yield SerializeEntity::from($this->manager);
        yield SerializeEmbedded::from($this->manager, $this->entity);
        yield SerializeCastable::from($this->entity, $this->castArgumentsMap);
        yield SerializeNoop::instance();
    }
}
