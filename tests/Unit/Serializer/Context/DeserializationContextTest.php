<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Context;

use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext
 */
final class DeserializationContextTest extends TestCase
{
    public function testHoldsAllProperties(): void
    {
        $context = DeserializationContext::make()
            ->withFieldName('fieldName')
            ->withType('TypeName')
            ->withSerialized(['serialized' => 'data'])
            ->withName('serialize_as');

        self::assertSame('fieldName', $context->getFieldName());
        self::assertSame('TypeName', $context->getType());
        self::assertSame(['serialized' => 'data'], $context->getSerialized());
        self::assertSame('serialize_as', $context->getName());
    }

    public function testUsesFieldNameAsNameIfLatterNotPresent(): void
    {
        $context = DeserializationContext::make()
            ->withFieldName('fieldName')
            ->withType('TypeName')
            ->withSerialized(['serialized' => 'data']);

        self::assertSame('fieldName', $context->getFieldName());
        self::assertSame('TypeName', $context->getType());
        self::assertSame(['serialized' => 'data'], $context->getSerialized());
        self::assertSame('fieldName', $context->getName());
    }
}
