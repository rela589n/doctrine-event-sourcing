<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ClassMetadata;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToDatabaseValue;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Types\TypeIsEmbedded;

#[Immutable]
final class SerializeEmbedded implements SeparateSerializer
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ClassMetadata $entityMetadata,
        private TypeIsEmbedded $typeIsEmbedded,
        private ConvertToDatabaseValue $convertToDatabaseValue,
    ) {
    }

    public static function from(EntityManagerInterface $manager, AggregateRoot $entity): self
    {
        return new self(
            $manager,
            $manager->getClassMetadata($entity::class),
            new TypeIsEmbedded\Impl($manager),
            ConvertToDatabaseValue\Impl::fromEntityManager($manager),
        );
    }

    public function isPossible(SerializationContext $context): bool
    {
        $value = $context->getValue();

        return is_object($value)
            && ($this->typeIsEmbedded)($value::class);
    }

    public function __invoke(SerializationContext $context): array
    {
        $value = $context->getValue();

        $valueMeta = $this->manager->getClassMetadata($value::class);

        $serialized = [];

        foreach ($this->entityMetadata->fieldMappings as $data) {
            if (($data['originalClass'] ?? null) !== $value::class) {
                continue;
            }

            [
                'type' => $dbalType,
                'columnName' => $columnName,
                'originalField' => $originalField,
            ] = $data;

            $reflectedValue = $valueMeta
                ->reflFields[$originalField]
                ->getValue($value);

            $serialized[$columnName] = ($this->convertToDatabaseValue)($dbalType, $reflectedValue);
        }

        return $serialized;
    }
}
