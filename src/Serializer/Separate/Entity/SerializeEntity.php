<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity;

use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToDatabaseValue;

#[Immutable]
final class SerializeEntity implements SeparateSerializer
{
    public function __construct(
        private EntityManagerInterface $em,
        private ConvertToDatabaseValue $convertToDatabaseValue,
    ) {
    }

    public static function from(EntityManagerInterface $manager): self
    {
        return new self($manager, ConvertToDatabaseValue\Impl::fromEntityManager($manager));
    }

    public function isPossible(SerializationContext $context): bool
    {
        return $context->getValue() instanceof AggregateRoot;
    }

    public function __invoke(SerializationContext $context): mixed
    {
        /** @var AggregateRoot $value */
        $value = $context->getValue();

        $typeName = $this->em->getClassMetadata($value::class)
                             ->getFieldMapping($value::getPrimaryName())['type'];

        return ($this->convertToDatabaseValue)($typeName, $value->getPrimary());
    }
}
