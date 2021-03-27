<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateDeserializer;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToPHPValue;

final class DeserializeTyped implements SeparateDeserializer
{
    public function __construct(
        private ConvertToPHPValue $convertToPHPValue,
        private array $propertiesTypes,
    ) {
        $this->propertiesTypes = array_map(static fn(Type $type) => $type, $this->propertiesTypes);
    }

    public static function from(EntityManagerInterface $manager, array $propertiesTypes): self
    {
        return new self(ConvertToPHPValue::fromEntityManager($manager), $propertiesTypes);
    }

    public function isPossible(DeserializationContext $context): bool
    {
        return isset($this->propertiesTypes[$context->getFieldName()]);
    }

    public function __invoke(DeserializationContext $context): mixed
    {
        $fieldName = $context->getFieldName();
        $serialized = $context->getSerialized();
        $name = $context->getName();

        return ($this->convertToPHPValue)($this->propertiesTypes[$fieldName], $serialized[$name]);
    }
}
