<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Noop;

use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\DeserializeNoop;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\SerializeNoop;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\SerializeNoop
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\DeserializeNoop
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext
 */
final class NoopSerializerTest extends TestCase
{
    public function testNoopSerializeIsAlwaysPossible(): void
    {
        $serialize = SerializeNoop::instance();
        $context = SerializationContext::make();

        self::assertTrue($serialize->isPossible($context));
    }

    public function testReturnsSameValueWhenSerializing(): void
    {
        $serialize = SerializeNoop::instance();

        $context = SerializationContext::make()
            ->withValue('the value');

        self::assertTrue($serialize->isPossible($context));
        self::assertSame('the value', $serialize($context));
    }

    public function testNoopDeserializeIsPossibleWhenHasSuchSerializedField(): void
    {
        $deserialize = DeserializeNoop::instance();
        $context = DeserializationContext::make()
            ->withFieldName('field')
            ->withSerialized(['field' => 'serialized']);
        self::assertTrue($deserialize->isPossible($context));
    }

    public function testNoopDeserializeIsNotPossibleWhenHasNoSuchSerializedField(): void
    {
        $deserialize = DeserializeNoop::instance();
        $context = DeserializationContext::make()
            ->withFieldName('field')
            ->withSerialized(['another' => 'serialized']);

        self::assertFalse($deserialize->isPossible($context));
    }

    public function testReturnsOriginalSerializedValueWhenDeserializing(): void
    {
        $deserialize = DeserializeNoop::instance();
        $context = DeserializationContext::make()
            ->withFieldName('someField')
            ->withSerialized(['someField' => 'serialized']);

        self::assertTrue($deserialize->isPossible($context));
        self::assertSame('serialized', $deserialize($context));
    }
}
