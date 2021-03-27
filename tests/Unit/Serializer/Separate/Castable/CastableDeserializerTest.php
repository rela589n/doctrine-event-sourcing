<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Castable;

use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\Castable;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\CastsAttributes;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\DeserializeCastable;
use Tests\Unit\Serializer\Separate\Castable\Mocks\CastableValueObject;

final class CastableDeserializerTest extends TestCase
{
    public function testCanBeDeserializedIfImplementsCastable(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $castable = $this->createMock(Castable::class);
        $serialize = DeserializeCastable::from($entity);

        self::assertTrue($serialize->isPossible(new DeserializationContext('', $castable::class, [])));
    }

    public function testCantBeDeserializedIfDoesntImplementCastable(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $notCastable = $this->createMock(AggregateRoot::class);
        $serialize = DeserializeCastable::from($entity);

        self::assertFalse($serialize->isPossible(new DeserializationContext('', $notCastable::class, [])));
    }

    public function testUsesCasterToDeserialize(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $castableVO = new CastableValueObject();
        $caster = $this->createMock(CastsAttributes::class);
        $caster->method('get')
               ->willReturnMap(
                   [[$entity, 'property', 'property_value', ['property' => 'property_value'], $castableVO]]
               );
        $castableVO::setCaster($caster);
        $deserialize = DeserializeCastable::from($entity);

        self::assertSame(
            $castableVO,
            $deserialize->__invoke(
                new DeserializationContext(
                    'property',
                    CastableValueObject::class,
                    ['property' => ['property' => 'property_value'], 'another' => ['another data']],
                )
            ),
        );
    }

    public function testPassesArgumentsToCastableByName(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $castableVO = new CastableValueObject();
        $caster = $this->createMock(CastsAttributes::class);
        $castableVO::setCaster($caster);
        $caster->method('set')->willReturn(null);

        $deserialize = DeserializeCastable::from($entity, ['first' => ['some', 'values']]);

        $deserialize(new DeserializationContext('first', CastableValueObject::class, ['first' => ['name' => 'value']]));
        self::assertSame(['some', 'values'], $castableVO::releaseArguments());
    }

    public function testPassesArgumentsToCastableByTypeIfNoNamePresent(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $castableVO = new CastableValueObject();
        $caster = $this->createMock(CastsAttributes::class);
        $castableVO::setCaster($caster);
        $caster->method('set')->willReturn(null);

        $deserialize = DeserializeCastable::from($entity, [CastableValueObject::class => ['some', 'values']]);
        $deserialize(new DeserializationContext('prop', CastableValueObject::class, ['prop' => ['name' => 'value']]));

        self::assertSame(['some', 'values'], $castableVO::releaseArguments());
    }
}
