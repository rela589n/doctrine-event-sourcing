<?php

declare(strict_types=1);


namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Converter;

use Rela589n\DoctrineEventSourcing\Serializer\Util\Database\GetDatabasePlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;

/* @final */class ConvertToPHPValue
{
    public function __construct(private AbstractPlatform $platform) { }

    public static function fromEntityManager(EntityManagerInterface $manager): static
    {
        return new static((new GetDatabasePlatform($manager))());
    }

    public function __invoke(string|Type $type, mixed $value): mixed
    {
        $type = is_string($type) ? $type : $type->getName();

        return Type::getType($type)
                   ->convertToPHPValue(
                       $value,
                       $this->platform,
                   );
    }
}
