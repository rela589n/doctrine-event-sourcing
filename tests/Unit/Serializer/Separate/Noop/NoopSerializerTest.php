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
    public function testReturnsSameValueWhenSerializing(): void
    {
        $serialize = SerializeNoop::instance();

        $context = SerializationContext::make()
            ->withFieldName('whatever')
            ->withValue('the value')
            ->withAttributes(['whatever']);

        self::assertTrue($serialize->isPossible($context));
        self::assertSame('the value', $serialize($context));
    }

    public function testReturnsOriginalSerializedValueWhenDeserializing(): void
    {
        $deserialize = DeserializeNoop::instance();
        $context = DeserializationContext::make()
            ->withFieldName('whatever')
            ->withType('whatever')
            ->withSerialized(['serialized']);
        self::assertTrue($deserialize->isPossible($context));
        self::assertSame(['serialized'], $deserialize($context));
    }
}
