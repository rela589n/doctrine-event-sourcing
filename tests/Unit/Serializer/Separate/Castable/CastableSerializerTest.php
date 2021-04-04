<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Castable;

use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\Castable;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\CastsAttributes;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\SerializeCastable;
use Tests\Unit\Serializer\Separate\Castable\Mocks\CastableValueObject;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\SerializeCastable
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext
 */
final class CastableSerializerTest extends TestCase
{
    public function testCanBeSerializedIfImplementsCastable(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $castable = $this->createMock(Castable::class);
        $serialize = SerializeCastable::from($entity);

        self::assertTrue(
            $serialize->isPossible(
                SerializationContext::make()
                    ->withValue($castable)
            )
        );
    }

    public function testCantBeSerializedIfDoesntImplementCastable(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $notCastable = $this->createMock(AggregateRoot::class);
        $serialize = SerializeCastable::from($entity);

        self::assertFalse(
            $serialize->isPossible(
                SerializationContext::make()
                    ->withValue($notCastable)
            )
        );
    }

    public function testUsesCasterToSerialize(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $castableVO = new CastableValueObject();
        $caster = $this->createMock(CastsAttributes::class);
        $caster->method('set')
            ->willReturnMap([[$entity, 'property', $castableVO, ['name' => 'value'], ['to_be' => 'returned']]]);
        $castableVO::setCaster($caster);
        $serialize = SerializeCastable::from($entity);

        self::assertSame(
            ['to_be' => 'returned'],
            $serialize(
                SerializationContext::make()
                    ->withFieldName('property')
                    ->withValue($castableVO)
                    ->withAttributes(['property' => ['name' => 'value'], 'another' => 'ff023cc8'])
            ),
        );
    }

    public function testPassesArgumentsToCastableByName(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $castableVO = new CastableValueObject();
        $caster = $this->createMock(CastsAttributes::class);
        $castableVO::setCaster($caster);
        $caster->method('set')
            ->willReturn(null);

        $serialize = SerializeCastable::from($entity, ['first' => ['some', 'values']]);
        $serialize(
            SerializationContext::make()
                ->withFieldName('first')
                ->withValue($castableVO)
                ->withAttributes(['first' => ['name' => 'value']])
        );

        self::assertSame(['some', 'values'], $castableVO::releaseArguments());
    }

    public function testPassesArgumentsToCastableByValueClassIfNoNamePresent(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $castableVO = new CastableValueObject();
        $caster = $this->createMock(CastsAttributes::class);
        $castableVO::setCaster($caster);
        $caster->method('set')
            ->willReturn(null);

        $serialize = SerializeCastable::from($entity, [CastableValueObject::class => ['some', 'values']]);
        $serialize(
            SerializationContext::make()
                ->withFieldName('prop')
                ->withValue($castableVO)
                ->withAttributes(['prop' => ['name' => 'value']])
        );

        self::assertSame(['some', 'values'], $castableVO::releaseArguments());
    }

    public function testDoesntFailIfNoAttributesForNameFound(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        $castableVO = new CastableValueObject();
        $caster = $this->createMock(CastsAttributes::class);
        $castableVO::setCaster($caster);
        $caster->method('set')
            ->willReturn(null);

        $serialize = SerializeCastable::from($entity, [CastableValueObject::class => ['some', 'values']]);
        $serialize(
            SerializationContext::make()
                ->withFieldName('prop')
                ->withValue($castableVO)
                ->withAttributes([])
        );

        self::assertSame(['some', 'values'], $castableVO::releaseArguments());
    }
}
