<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Typed;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed\SerializeTyped;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToDatabaseValue;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed\SerializeTyped
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext
 */
final class TypedSerializerTest extends TestCase
{
    private MockObject|EntityManagerInterface $manager;
    private MockObject|ConvertToDatabaseValue $convertToDatabaseValue;
    private SerializeTyped $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEntityManager();
        $this->convertToDatabaseValue = $this->createMock(ConvertToDatabaseValue::class);
    }

    public function testCanBeSerializedIfHasRegisteredType(): void
    {
        $this->setUpSerializer(
            [
                'property' => Type::getType(Types::DATETIMETZ_IMMUTABLE),
            ]
        );

        self::assertTrue(
            $this->serializer->isPossible(
                SerializationContext::make()
                    ->withFieldName('property')
            )
        );
    }

    public function testCantBeSerializedIfHasNoRegisteredType(): void
    {
        $this->setUpSerializer(
            [
                'another' => Type::getType(Types::JSON),
            ]
        );

        self::assertFalse(
            $this->serializer->isPossible(
                SerializationContext::make()
                    ->withFieldName('property')
            )
        );
    }

    public function testSerializeUsingType(): void
    {
        $type = Type::getType(Types::BOOLEAN);
        $this->setUpSerializer(['another' => $type]);

        $this->convertToDatabaseValue
            ->method('__invoke')
            ->willReturnMap([[$type, true, 'true']]);

        self::assertSame(
            'true',
            $this->serializer->__invoke(
                SerializationContext::make()
                    ->withFieldName('another')
                    ->withValue(true)
            ),
        );
    }

    private function setupEntityManager()
    {
        $this->manager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $platform = $this->createMock(AbstractPlatform::class);

        $connection->method('getDatabasePlatform')
            ->willReturn($platform);

        $this->manager->method('getConnection')
            ->willReturn($connection);
    }

    private function setUpSerializer(array $propertiesTypes): void
    {
        $this->serializer = new SerializeTyped(
            $this->convertToDatabaseValue,
            $propertiesTypes,
        );
    }
}
