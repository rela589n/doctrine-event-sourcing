<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class HideFromPayload
{
}
