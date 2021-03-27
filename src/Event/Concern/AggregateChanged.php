<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event\Concern;

use DateTimeImmutable;
use DateTimeInterface as DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use ReflectionClass;
use ReflectionProperty;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Event\Annotations\HideFromPayload;
use Rela589n\DoctrineEventSourcing\Event\Concern\TypesMeta\CollectEventSerializeMeta;
use Rela589n\DoctrineEventSourcing\Event\Concern\TypesMeta\CollectEventSerializeMetaImpl;
use Rela589n\DoctrineEventSourcing\Event\Concern\TypesMeta\CollectEventSerializeMetaInMemoryCacheDecorator;
use Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer;
use Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer;
use Rela589n\DoctrineEventSourcing\Serializer\Composed\DeserializeProperty;
use Rela589n\DoctrineEventSourcing\Serializer\Composed\SerializeProperty;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;

/** @mixin \Rela589n\DoctrineEventSourcing\Event\AggregateChanged */
trait AggregateChanged
{
    #[HideFromPayload]
    protected int $id;

    #[HideFromPayload]
    protected string $name;

    #[HideFromPayload]
    protected DateTime $timestamp;

    #[HideFromPayload]
    protected array $payload;

    #[HideFromPayload]
    protected AggregateRoot $entity;

    #[HideFromPayload]
    private ?CollectEventSerializeMeta $collectPropertiesMeta = null;

    #[HideFromPayload]
    private ?ComposedDeserializer $deserializerCached = null;

    #[HideFromPayload]
    private ?ComposedSerializer $serializerCached = null;

    public function __construct(AggregateRoot $entity)
    {
        $this->entity = $entity;
        $this->timestamp = new DateTimeImmutable();
        $this->name = $this->NAME();
        $this->payload = [];
    }

    protected function collectPropertiesMeta(): CollectEventSerializeMeta
    {
        return $this->collectPropertiesMeta
            ?? $this->collectPropertiesMeta = $this->makeMetaCollector();
    }

    protected function makeMetaCollector(): CollectEventSerializeMeta
    {
        return new CollectEventSerializeMetaInMemoryCacheDecorator(
            new CollectEventSerializeMetaImpl(),
        );
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function onPreFlush(PreFlushEventArgs $args): void
    {
        $reflectionClass = $this->reflectionClass($args->getEntityManager());
        $properties = $this->properties($reflectionClass);

        $serialize = $this->serializer($args->getEntityManager(), $properties);

        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $property->getValue($this);

            $context = new SerializationContext($name, $value, $this->payload);
            ['name' => $saveUnderName, 'value' => $serialized] = $serialize($context);
            $this->payload[$saveUnderName] = $serialized;
        }
    }

    public function onPostLoad(LifecycleEventArgs $args): void
    {
        $reflectionClass = $this->reflectionClass($args->getEntityManager());
        $properties = $this->properties($reflectionClass);

        $deserialize = $this->deserializer($args->getEntityManager(), $properties);

        foreach ($properties as $property) {
            $name = $property->getName();
            $typename = (string)$property->getType()?->getName();

            $context = new DeserializationContext($name, $typename, $this->payload);
            $property->setValue($this, $deserialize($context));
        }
    }

    /** @return ReflectionProperty[] */
    protected function properties(ReflectionClass $reflectionClass): array
    {
        $properties = array_filter(
            $reflectionClass->getProperties(),
            fn(ReflectionProperty $property) => !$this->shouldNotSerialize($property)
        );

        array_walk($properties, static fn(ReflectionProperty $property) => $property->setAccessible(true));

        return $properties;
    }

    protected function shouldNotSerialize(ReflectionProperty $property): bool
    {
        return $property->isStatic()
            || !empty($property->getAttributes(HideFromPayload::class));
    }

    protected function reflectionClass(EntityManagerInterface $manager): ReflectionClass
    {
        return $manager->getClassMetadata(static::class)->reflClass;
    }

    protected function serializer(EntityManagerInterface $manager, array $properties): ComposedSerializer
    {
        return $this->serializerCached
            ?? $this->serializerCached = $this->makeSerializer($manager, $properties);
    }

    protected function makeSerializer(EntityManagerInterface $manager, array $properties): ComposedSerializer
    {
        return new SerializeProperty(
            $manager,
            $this->entity,
            $this->collectPropertiesMeta()(...$properties),
            $this->castArguments(),
        );
    }

    protected function deserializer(EntityManagerInterface $manager, array $properties): ComposedDeserializer
    {
        return $this->deserializerCached ??
            $this->deserializerCached = $this->makeDeserializer($manager, $properties);
    }

    protected function makeDeserializer(EntityManagerInterface $manager, array $properties): ComposedDeserializer
    {
        return new DeserializeProperty(
            $manager,
            $this->entity,
            $this->collectPropertiesMeta()(...$properties),
            $this->castArguments(),
        );
    }

    protected function castArguments(): array
    {
        return [];
    }
}
