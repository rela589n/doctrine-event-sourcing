<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Entity;

interface AggregateRoot
{
    public static function getPrimaryName(): string;

    public function getPrimary(): mixed;
}
