<?php

declare(strict_types=1);


namespace Rela589n\DoctrineEventSourcing\Serializer\Util\Types;

use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;

/* @final */class ResolvePrimaryType
{
    public function __construct(
        protected EntityManagerInterface $manager
    ) {
    }

    /** @param  string|AggregateRoot  $className */
    public function __invoke(string $className): Type
    {
        return Type::getType(
            $this->manager
                ->getClassMetadata($className)
                ->getFieldMapping($className::getPrimaryName())['type']
        );
    }
}
