<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable;

use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\Castable;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\CastsAttributes;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;

final class SerializeCastable implements SeparateSerializer
{
    public function __construct(
        private AggregateRoot $entity,
        private array $castArguments = [],
    ) {
    }

    public static function from(AggregateRoot $entity, array $castArguments = []): self
    {
        return new self($entity, $castArguments);
    }

    public function isPossible(SerializationContext $context): bool
    {
        return $context->getValue() instanceof Castable;
    }

    public function __invoke(SerializationContext $context): mixed
    {
        $fieldName = $context->getFieldName();
        $name = $context->getName();
        $value = $context->getValue();
        $attributes = $context->getAttributes();

        /** @var CastsAttributes $caster */
        $caster = $value::castUsing($this->castArguments[$fieldName] ?? $this->castArguments[$value::class] ?? []);

        return $caster->set($this->entity, $fieldName, $value, $attributes[$name]);
    }
}
