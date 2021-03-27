<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed;

use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToDatabaseValue;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;

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

    public function isPossible(string $name, mixed $value, array $attributes): bool
    {
        return isset($this->propertiesTypes[$name]);
    }

    public function __invoke(string $name, mixed $value, array $attributes): mixed
    {
        return ($this->convertToDatabaseValue)($this->propertiesTypes[$name], $value);
    }
}
