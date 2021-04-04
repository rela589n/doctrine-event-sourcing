<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Composed;

use LogicException;
use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Pipeline\SerializationContextPipe;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\SerializeNoop;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateSerializer;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Separate\Noop\SerializeNoop
 */
final class ComposedSerializerTest extends TestCase
{
    public function testPassesContextThroughPipes(): void
    {
        $originalContext = SerializationContext::make()
            ->withFieldName('original')
            ->withValue(0)
            ->withAttributes(['key' => 'value']);

        $pipe1 = $this->createMock(SerializationContextPipe::class);
        $context1 = SerializationContext::make()
            ->withFieldName('pipe1')
            ->withValue(1)
            ->withAttributes(['key' => 'value amended']);
        $pipe1->method('__invoke')
            ->willReturnMap([[$originalContext, $context1]]);

        $pipe2 = $this->createMock(SerializationContextPipe::class);
        $context2 = SerializationContext::make()
            ->withFieldName('pipe2')
            ->withValue(2)
            ->withAttributes(['key' => 'value amended2']);
        $pipe2->method('__invoke')
            ->willReturnMap([[$context1, $context2]]);

        $serializer = new ComposedSerializer(fn() => [$pipe1, $pipe2], fn() => [SerializeNoop::instance()]);
        $result = $serializer($originalContext);
        self::assertSame(
            [
                'name' => $context2->getName(),
                'value' => 2,
            ],
            $result,
        );
    }

    public function testSelectsFirstPossibleSerializer(): void
    {
        $context = SerializationContext::make()
            ->withFieldName('original')
            ->withValue(0)
            ->withAttributes(['key' => 'value']);
        $e = new LogicException('Should not match');

        $separateSerializer1 = $this->createMock(SeparateSerializer::class);
        $separateSerializer1->method('isPossible')
            ->willReturnMap([[$context, false]]);
        $separateSerializer1->method('__invoke')
            ->willThrowException($e);

        $separateSerializer2 = $this->createMock(SeparateSerializer::class);
        $separateSerializer2->method('isPossible')
            ->willReturnMap([[$context, true]]);
        $separateSerializer2->method('__invoke')
            ->willReturnMap([[$context, 'result value']]);

        $separateSerializer3 = $this->createMock(SeparateSerializer::class);
        $separateSerializer3->method('isPossible')
            ->willThrowException($e);
        $separateSerializer3->method('__invoke')
            ->willThrowException($e);

        $serializer = new ComposedSerializer(
            fn() => [],
            fn() => [$separateSerializer1, $separateSerializer2, $separateSerializer3],
        );

        $serialized = $serializer($context);

        self::assertSame(
            [
                'name' => 'original',
                'value' => 'result value',
            ],
            $serialized,
        );
    }

    public function testThrowsExceptionIfNoSerializersProvided(): void
    {
        $pipe = $this->createMock(SerializationContextPipe::class);
        $context = SerializationContext::make()
            ->withFieldName('')
            ->withValue(0)
            ->withAttributes([]);

        $pipe->method('__invoke')
            ->willReturnMap([[$context, $context]]);
        $serializer = new ComposedSerializer(fn() => [$pipe], fn() => []);

        $this->expectException(LogicException::class);
        $serializer($context);
    }

    public function testPassesContextIntoPipesAndSerializersProviders(): void
    {
        $pipe = $this->createMock(SerializationContextPipe::class);
        $context = SerializationContext::make()
            ->withFieldName('field')
            ->withValue(0)
            ->withAttributes([]);

        $pipedContext = clone $context;
        $pipe->method('__invoke')
            ->willReturnMap([[$context, $pipedContext]]);

        $serializer = new ComposedSerializer(
            function (SerializationContext $c) use ($context, $pipe) {
                self::assertSame($context, $c);

                return [$pipe];
            },
            function (SerializationContext $c) use ($pipedContext) {
                self::assertSame($pipedContext, $c);

                return [SerializeNoop::instance()];
            }
        );

        $arr = $serializer($context);
        self::assertSame(['name' => 'field', 'value' => 0], $arr);
    }
}
