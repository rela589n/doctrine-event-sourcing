<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event\Annotations;

use Attribute;
use Doctrine\DBAL\Types\Type;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class SerializeAs
{
    private ?Type $type;
    private ?string $name;

    public function __construct(?string $type = null, ?string $name = null)
    {
        $this->name = $name;
        $this->type = (null !== $type)
            ? Type::getType($type)
            : null;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
