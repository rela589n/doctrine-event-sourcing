<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Context;

use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext
 */
final class SerializationContextTest extends TestCase
{
    public function testHoldsAllData(): void
    {
        $context = SerializationContext::make()
            ->withFieldName('fieldName')
            ->withValue('some value')
            ->withAttributes(['some' => 'data'])
            ->withName('save_with_name');

        self::assertSame('fieldName', $context->getFieldName());
        self::assertSame('some value', $context->getValue());
        self::assertSame(['some' => 'data'], $context->getAttributes());
        self::assertSame('save_with_name', $context->getName());
    }

    public function testUsesFieldNameAsNameIfLatterNotPresent(): void
    {
        $context = SerializationContext::make()
            ->withFieldName('fieldName')
            ->withValue('some value')
            ->withAttributes(['some' => 'data']);

        self::assertSame('fieldName', $context->getFieldName());
        self::assertSame('some value', $context->getValue());
        self::assertSame(['some' => 'data'], $context->getAttributes());
        self::assertSame('fieldName', $context->getName());
    }
}
