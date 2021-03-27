<?php

declare(strict_types=1);

namespace Tests\Mocks;

use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;

final class AggregateRootMock implements AggregateRoot
{
    private static ?string $primaryName = null;

    public function __construct(private $primary) { }

    public static function setPrimaryName(string $primaryName): void
    {
        self::$primaryName = $primaryName;
    }

    public static function getPrimaryName(): string
    {
        return self::$primaryName;
    }

    public function getPrimary(): mixed
    {
        return $this->primary;
    }
}
