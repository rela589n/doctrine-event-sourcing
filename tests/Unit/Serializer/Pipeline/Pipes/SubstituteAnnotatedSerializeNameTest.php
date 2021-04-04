<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Pipeline\Pipes;

use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes\SubstituteAnnotatedSerializeName;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes\SubstituteAnnotatedSerializeName
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext
 */
final class SubstituteAnnotatedSerializeNameTest extends TestCase
{
    public function testReturnsSameContextIfNoMetaForFieldFound(): void
    {
        $context = SerializationContext::make()
            ->withFieldName('field')
            ->withValue('')
            ->withAttributes([]);

        $substitute = new SubstituteAnnotatedSerializeName(['field2' => 'whatever']);
        $result = $substitute($context);

        self::assertSame($context, $result);
    }

    public function testSubstitutesNameIfNameMetaPresent(): void
    {
        $context = SerializationContext::make()
            ->withFieldName('field')
            ->withValue(123123)
            ->withAttributes(['some' => 'data']);

        $substitute = new SubstituteAnnotatedSerializeName(['field' => 'whatever']);
        $result = $substitute($context);

        self::assertNotSame($context, $result);
        self::assertSame('whatever', $result->getName());
        self::assertSame('field', $result->getFieldName());
        self::assertSame(123123, $result->getValue());
        self::assertSame(['some' => 'data'], $result->getAttributes());
    }
}
