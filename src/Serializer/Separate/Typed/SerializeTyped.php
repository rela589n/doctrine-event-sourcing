<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToDatabaseValue;

#[Immutable]
final class SerializeTyped implements SeparateSerializer
{
    public function __construct(
        private ConvertToDatabaseValue $convertToDatabaseValue,
        private array $propertiesTypes,
    ) {
        $this->propertiesTypes = array_map(static fn(Type $type) => $type, $this->propertiesTypes);
    }

    public static function from(EntityManagerInterface $manager, array $propertiesTypes): self
    {
        return new self(ConvertToDatabaseValue::fromEntityManager($manager), $propertiesTypes);
    }

    public function isPossible(SerializationContext $context): bool
    {
        return isset($this->propertiesTypes[$context->getFieldName()]);
    }

    public function __invoke(SerializationContext $context): mixed
    {
        return ($this->convertToDatabaseValue)($this->propertiesTypes[$context->getFieldName()], $context->getValue());
    }
}
