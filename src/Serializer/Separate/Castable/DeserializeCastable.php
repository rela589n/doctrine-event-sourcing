<?php

declare(strict_types=1);


namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable;

use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\Castable;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\CastsAttributes;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateDeserializer;

final class DeserializeCastable implements SeparateDeserializer
{
    public function __construct(
        private AggregateRoot $entity,
        private array $castArguments,
    ) {
    }

    public static function from(AggregateRoot $entity, array $castArguments = []): self
    {
        return new self($entity, $castArguments);
    }

    public function isPossible(DeserializationContext $context): bool
    {
        return is_subclass_of($context->getType(), Castable::class);
    }

    public function __invoke(DeserializationContext $context): mixed
    {
        /** @var string|Castable $type */
        $name = $context->getName();
        $type = $context->getType();
        $serialized = $context->getSerialized();

        /** @var CastsAttributes $caster */
        $caster = $type::castUsing($this->castArguments[$name] ?? $this->castArguments[$type] ?? []);

        $attributes = $serialized[$name];

        return $caster->get($this->entity, $name, $attributes[$name] ?? null, $attributes);
    }
}
