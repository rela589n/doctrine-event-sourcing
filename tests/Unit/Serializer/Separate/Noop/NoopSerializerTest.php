<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Noop;

use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\DeserializeNoop;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\SerializeNoop;

final class NoopSerializerTest extends TestCase
{
    public function testReturnsSameValueWhenSerializing(): void
    {
        $serialize = SerializeNoop::instance();

        $context = new SerializationContext('whatever', 'the value', ['whatever']);
        self::assertTrue($serialize->isPossible($context));
        self::assertSame('the value', $serialize($context));
    }

    public function testReturnsOriginalSerializedValueWhenDeserializing(): void
    {
        $deserialize = DeserializeNoop::instance();
        $context = new DeserializationContext('whatever', 'whatever', ['serialized']);
        self::assertTrue($deserialize->isPossible($context));
        self::assertSame(['serialized'], $deserialize($context));
    }
}
