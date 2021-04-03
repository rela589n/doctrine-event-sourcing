<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToDatabaseValue;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToDatabaseValue;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Database\GetDatabasePlatform;

#[Immutable]
final class Impl implements ConvertToDatabaseValue
{
    public function __construct(protected AbstractPlatform $platform) { }

    public static function fromEntityManager(EntityManagerInterface $manager): self
    {
        return new self((new GetDatabasePlatform\Impl($manager))());
    }

    public function __invoke(string|Type $type, mixed $value): mixed
    {
        $type = is_string($type) ? $type : $type->getName();

        return Type::getType($type)
                   ->convertToDatabaseValue(
                       $value,
                       $this->platform,
                   );
    }
}
