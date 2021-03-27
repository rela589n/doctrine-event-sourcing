<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event\Annotations;

use Attribute;
use Doctrine\DBAL\Types\Type;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class SerializeAs
{
    private Type $type;
    private ?string $name;

    public function __construct(string $type, ?string $name = null)
    {
        $this->type = Type::getType($type);
        $this->name = $name;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
