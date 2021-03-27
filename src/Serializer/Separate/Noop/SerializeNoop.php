<?php

declare(strict_types=1);


namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop;

use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;

final class SerializeNoop implements SeparateSerializer
{
    private static $instance = null;

    private function __construct() { }

    public static function instance(): self
    {
        return self::$instance
            ?? self::$instance = new self();
    }

    public function isPossible(SerializationContext $context): bool
    {
        return true;
    }

    public function __invoke(SerializationContext $context): mixed
    {
        return $context->getValue();
    }
}
