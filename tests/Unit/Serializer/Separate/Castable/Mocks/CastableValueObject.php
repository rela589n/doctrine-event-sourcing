<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Castable\Mocks;

use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\Castable;

final class CastableValueObject implements Castable
{
    private static array $arguments = [];
    public static $caster = null;

    public static function setCaster($caster): void
    {
        self::$caster = $caster;
    }

    public static function castUsing(array $arguments)
    {
        self::$arguments = $arguments;
        $caster = self::$caster;
        self::$caster = null;
        return $caster;
    }

    public static function releaseArguments(): array
    {
        $arguments = self::$arguments;
        self::$arguments = [];
        return $arguments;
    }
}
