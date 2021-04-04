<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Composed;

use LogicException;
use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\DeserializationContextPipe;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\DeserializeNoop;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateDeserializer;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\DeserializeNoop
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer\Factory\Context
 */
final class ComposedDeserializerTest extends TestCase
{
    public function testPassesContextThroughPipes(): void
    {
        $originalContext = DeserializationContext::make()
            ->withFieldName('original')
            ->withSerialized('serialized original');

        $pipe1 = $this->createMock(DeserializationContextPipe::class);
        $context1 = DeserializationContext::make()
            ->withFieldName('pipe1')
            ->withSerialized('serialized amended');
        $pipe1->method('__invoke')
            ->willReturnMap([[$originalContext, $context1]]);

        $pipe2 = $this->createMock(DeserializationContextPipe::class);
        $context2 = DeserializationContext::make()
            ->withFieldName('pipe2')
            ->withSerialized('serialized amended 2');
        $pipe2->method('__invoke')
            ->willReturnMap([[$context1, $context2]]);

        $deserializer = new ComposedDeserializer(fn() => [$pipe1, $pipe2], fn() => [DeserializeNoop::instance()]);
        $deserialized = $deserializer($originalContext);

        self::assertSame('serialized amended 2', $deserialized);
    }

    public function testSelectsFirstPossibleDeserializer(): void
    {
        $context = DeserializationContext::make()
            ->withFieldName('original')
            ->withSerialized('serialized original');
        $e = new LogicException('Should not match');

        $separateDeserializer1 = $this->createMock(SeparateDeserializer::class);
        $separateDeserializer1->method('isPossible')
            ->willReturnMap([[$context, false]]);
        $separateDeserializer1->method('__invoke')
            ->willThrowException($e);
        $separateDeserializer2 = $this->createMock(SeparateDeserializer::class);
        $separateDeserializer2->method('isPossible')
            ->willReturnMap([[$context, true]]);
        $separateDeserializer2->method('__invoke')
            ->willReturnMap([[$context, 'result value']]);
        $separateDeserializer3 = $this->createMock(SeparateDeserializer::class);
        $separateDeserializer3->method('isPossible')
            ->willThrowException($e);
        $separateDeserializer3->method('__invoke')
            ->willThrowException($e);

        $deserializer = new ComposedDeserializer(
            fn() => [],
            fn() => [$separateDeserializer1, $separateDeserializer2, $separateDeserializer3],
        );

        $deserialized = $deserializer($context);

        self::assertSame('result value', $deserialized);
    }

    public function testThrowsExceptionIfNoDeserializersProvided(): void
    {
        $pipe = $this->createMock(DeserializationContextPipe::class);
        $context = DeserializationContext::make()
            ->withFieldName('')
            ->withSerialized('');
        $pipe->method('__invoke')
            ->willReturnMap([[$context, $context]]);
        $deserializer = new ComposedDeserializer(fn() => [$pipe], fn() => []);

        $this->expectException(LogicException::class);
        $deserializer($context);
    }

    public function testPassesContextIntoPipesAndDeserializersProviders(): void
    {
        $pipe = $this->createMock(DeserializationContextPipe::class);
        $context = DeserializationContext::make()
            ->withFieldName('fieldName')
            ->withSerialized('serialized value');

        $pipedContext = clone $context;
        $pipe->method('__invoke')
            ->willReturnMap([[$context, $pipedContext]]);

        $deserializer = new ComposedDeserializer(
            function (DeserializationContext $c) use ($context, $pipe) {
                self::assertSame($context, $c);

                return [$pipe];
            },
            function (DeserializationContext $c) use ($pipedContext) {
                self::assertSame($pipedContext, $c);

                return [DeserializeNoop::instance()];
            }
        );

        $arr = $deserializer($context);
        self::assertSame('serialized value', $arr);
    }
}
