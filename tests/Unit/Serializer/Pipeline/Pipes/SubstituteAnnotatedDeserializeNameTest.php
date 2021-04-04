<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Pipeline\Pipes;

use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes\SubstituteAnnotatedDeserializeName;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Pipeline\Pipes\SubstituteAnnotatedDeserializeName
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext
 */
final class SubstituteAnnotatedDeserializeNameTest extends TestCase
{
    public function testReturnsSameContextIfNoMetaForFieldFound(): void
    {
        $context = DeserializationContext::make()
            ->withFieldName('field')

            ->withSerialized([]);

        $substitute = new SubstituteAnnotatedDeserializeName(['field2' => 'whatever']);
        $result = $substitute($context);

        self::assertSame($context, $result);
    }

    public function testSubstitutesNameIfNameMetaPresent(): void
    {
        $context = DeserializationContext::make()
            ->withFieldName('field')
            ->withType('TypeName')
            ->withSerialized(['some' => 'data']);

        $substitute = new SubstituteAnnotatedDeserializeName(['field' => 'whatever']);
        $result = $substitute($context);

        self::assertNotSame($context, $result);
        self::assertSame('whatever', $result->getName());
        self::assertSame('field', $result->getFieldName());
        self::assertSame('TypeName', $result->getType());
        self::assertSame(['some' => 'data'], $result->getSerialized());
    }
}
