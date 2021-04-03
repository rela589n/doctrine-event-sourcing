<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToPHPValue;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToPHPValue;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Database\GetDatabasePlatform;

#[Immutable]
final class Impl implements ConvertToPHPValue
{
    public function __construct(private AbstractPlatform $platform) { }

    public static function fromEntityManager(EntityManagerInterface $manager): self
    {
        return new self((new GetDatabasePlatform\Impl($manager))());
    }

    public function __invoke(string|Type $type, mixed $value): mixed
    {
        $type = is_string($type) ? Type::getType($type) : $type;

        return $type->convertToPHPValue(
                       $value,
                       $this->platform,
                   );
    }
}
