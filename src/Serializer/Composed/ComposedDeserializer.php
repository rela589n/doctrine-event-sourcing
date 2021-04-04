<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Composed;

use Closure;
use JetBrains\PhpStorm\Immutable;
use LogicException;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\DeserializationContextPipe;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateDeserializer;

#[Immutable]
final class ComposedDeserializer
{
    /** @var Closure(DeserializationContext):iterable<DeserializationContextPipe> */
    private Closure $pipes;

    /** @var Closure(DeserializationContext):iterable<SeparateDeserializer> */
    private Closure $deserializers;

    public function __construct(Closure $pipes, Closure $deserializers)
    {
        $this->pipes = $pipes;
        $this->deserializers = $deserializers;
    }

    public function __invoke(DeserializationContext $context): mixed
    {
        /** @var DeserializationContextPipe $pipe */
        foreach (($this->pipes)($context) as $pipe) {
            $context = $pipe($context);
        }

        /** @var SeparateDeserializer $deserialize */
        foreach (($this->deserializers)($context) as $deserialize) {
            if ($deserialize->isPossible($context)) {
                return $deserialize($context);
            }
        }

        throw new LogicException("No deserializer matches context for '{$context->getName()}' property");
    }
}
