<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event\Annotations;

use Attribute;
use Doctrine\DBAL\Types\Type;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class SerializeAs
{
    private Type $type;

    public function __construct(string $type)
    {
        $this->type = Type::getType($type);
    }

    public function getType(): Type
    {
        return $this->type;
    }
}
