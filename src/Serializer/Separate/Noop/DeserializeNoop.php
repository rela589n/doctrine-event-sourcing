<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop;

use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateDeserializer;

final class DeserializeNoop implements SeparateDeserializer
{
    private static $instance = null;

    private function __construct() { }

    public static function instance(): self
    {
        return self::$instance
            ?? self::$instance = new self();
    }

    public function isPossible(DeserializationContext $context): bool
    {
        return true;
    }

    public function __invoke(DeserializationContext $context): mixed
    {
        return $context->getSerialized();
    }
}
