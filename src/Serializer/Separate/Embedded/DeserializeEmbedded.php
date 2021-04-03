<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ClassMetadata;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateDeserializer;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToPHPValue;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Types\TypeIsEmbedded;

#[Immutable]
final class DeserializeEmbedded implements SeparateDeserializer
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ClassMetadata $entityMetadata,
        private TypeIsEmbedded $typeIsEmbedded,
        private ConvertToPHPValue $convertToPHPValue,
    ) {
    }

    public static function from(EntityManagerInterface $manager, AggregateRoot $entity): self
    {
        return new self(
            $manager,
            $manager->getClassMetadata($entity::class),
            new TypeIsEmbedded\Impl($manager),
            ConvertToPHPValue\Impl::fromEntityManager($manager),
        );
    }

    public function isPossible(DeserializationContext $context): bool
    {
        return ($this->typeIsEmbedded)($context->getType());
    }

    public function __invoke(DeserializationContext $context): mixed
    {
        $name = $context->getName();
        $type = $context->getType();
        $serialized = $context->getSerialized();

        $valueMetadata = $this->manager->getClassMetadata($type);
        $instance = $valueMetadata->reflClass->newInstanceWithoutConstructor();

        foreach ($this->entityMetadata->fieldMappings as $data) {
            if (($data['originalClass'] ?? null) !== $type) {
                continue;
            }

            [
                'type' => $dbalType,
                'columnName' => $columnName,
                'originalField' => $originalField,
            ] = $data;

            $reflectedValue = ($this->convertToPHPValue)($dbalType, $serialized[$name][$columnName]);

            $valueMetadata->reflFields[$originalField]
                ->setValue($instance, $reflectedValue);
        }

        return $instance;
    }
}
