<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Composed;

use Closure;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Immutable;
use LogicException;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\SerializationContextPipe;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;

#[Immutable]
final class ComposedSerializer
{
    /** @var Closure(SerializationContext):iterable<SerializationContextPipe> */
    private Closure $pipes;

    /** @var Closure(SerializationContext):iterable<SeparateSerializer> */
    private Closure $serializers;

    public function __construct(Closure $pipes, Closure $serializers)
    {
        $this->pipes = $pipes;
        $this->serializers = $serializers;
    }

    #[ArrayShape(['name' => "string", 'value' => "mixed"])]
    public function __invoke(SerializationContext $context): array
    {
        /** @var SerializationContextPipe $pipe */
        foreach (($this->pipes)($context) as $pipe) {
            $context = $pipe($context);
        }

        /** @var SeparateSerializer $serialize */
        foreach (($this->serializers)($context) as $serialize) {
            if ($serialize->isPossible($context)) {
                return [
                    'name' => $context->getName(),
                    'value' => $serialize($context)
                ];
            }
        }

        throw new LogicException("No serializer matches context for '{$context->getFieldName()}' property");
    }
}
