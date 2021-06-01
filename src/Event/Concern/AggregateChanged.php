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
    private ?ComposedSerializer\Factory\Context $serializerFactoryContext = null;

    #[HideFromPayload]
    private ?ComposedSerializer\Factory $serializerFactory = null;

    #[HideFromPayload]
    private ?ComposedDeserializer\Factory\Context $deserializerFactoryContext = null;

    #[HideFromPayload]
    private ?ComposedDeserializer\Factory $deserializerFactory = null;

    public function __construct(AggregateRoot $entity)
    {
        $this->entity = $entity;
        $this->timestamp = new DateTimeImmutable();
        $this->name = static::NAME();
        $this->payload = [];
    }

    private function collectPropertiesMeta(): CollectEventSerializeMeta
    {
        return $this->collectPropertiesMeta
            ?? $this->collectPropertiesMeta = $this->makeMetaCollector();
    }

    private function makeMetaCollector(): CollectEventSerializeMeta
    {
        return new CollectEventSerializeMetaInMemoryCacheDecorator(
            new CollectEventSerializeMetaImpl(),
        );
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    /** @internal */
    final public function onPreFlushAggregateChanged(PreFlushEventArgs $args): void
    {
        $reflectionClass = $this->reflectionClass($args->getEntityManager());
        $properties = $this->properties($reflectionClass);

        $serialize = $this->serializerFactory(
            $this->serializerFactoryContext
            ?? $this->serializerFactoryContext =
                ComposedSerializer\Factory\Context::make()
                    ->withEntityManager($args->getEntityManager())
                    ->withEntity($this->entity)
                    ->withPropertiesMeta($this->collectPropertiesMeta()(... $properties))
                    ->withCastArgumentsMap($this->castArguments())
        )
            ->make();

        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $property->getValue($this);

            ['name' => $saveUnderName, 'value' => $serialized] = $serialize(
                SerializationContext::make()
                    ->withFieldName($name)
                    ->withValue($value)
                    ->withAttributes($this->payload)
            );
            $this->payload[$saveUnderName] = $serialized;
        }
    }

    /** @internal */
    final public function onPostLoadAggregateChanged(LifecycleEventArgs $args): void
    {
        $reflectionClass = $this->reflectionClass($args->getEntityManager());
        $properties = $this->properties($reflectionClass);

        $deserialize = $this
            ->deserializerFactory(
                $this->deserializerFactoryContext
                ?? $this->deserializerFactoryContext = ComposedDeserializer\Factory\Context::make()
                    ->withEntityManager($args->getEntityManager())
                    ->withEntity($this->entity)
                    ->withPropertiesMeta($this->collectPropertiesMeta()(...$properties))
                    ->withCastArgumentsMap($this->castArguments())
            )->make();

        foreach ($properties as $property) {
            $name = $property->getName();
            $typename = (string)$property->getType()?->getName();

            $property->setValue(
                $this,
                $deserialize(
                    DeserializationContext::make()
                        ->withFieldName($name)
                        ->withType($typename)
                        ->withSerialized($this->payload),
                )
            );
        }
    }

    /** @return ReflectionProperty[] */
    private function properties(ReflectionClass $reflectionClass): array
    {
        $properties = array_filter(
            $reflectionClass->getProperties(),
            fn(ReflectionProperty $property) => !$this->shouldNotSerialize($property)
        );

        array_walk($properties, static fn(ReflectionProperty $property) => $property->setAccessible(true));

        return $properties;
    }

    private function shouldNotSerialize(ReflectionProperty $property): bool
    {
        return $property->isStatic()
            || !empty($property->getAttributes(HideFromPayload::class));
    }

    private function reflectionClass(EntityManagerInterface $manager): ReflectionClass
    {
        return $manager->getClassMetadata(static::class)->reflClass;
    }

    private function serializerFactory(ComposedSerializer\Factory\Context $context): ComposedSerializer\Factory
    {
        return $this->serializerFactory
            ?? $this->serializerFactory = $this->makeSerializerFactory($context);
    }

    protected function makeSerializerFactory(ComposedSerializer\Factory\Context $context): ComposedSerializer\Factory
    {
        return ComposedSerializer\Factory\Impl::fromContext($context);
    }

    private function deserializerFactory(ComposedDeserializer\Factory\Context $context): ComposedDeserializer\Factory
    {
        return $this->deserializerFactory
            ?? $this->deserializerFactory = $this->makeDeserializerFactory($context);
    }

    protected function makeDeserializerFactory(ComposedDeserializer\Factory\Context $context): ComposedDeserializer\Factory
    {
        return ComposedDeserializer\Factory\Impl::fromContext($context);
    }

    protected function castArguments(): array
    {
        return [];
    }
}
