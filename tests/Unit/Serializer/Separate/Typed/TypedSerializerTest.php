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
use Tests\Unit\Serializer\Mocks\Converter\ConvertToDatabaseValueMock;

final class TypedSerializerTest extends TestCase
{
    private EntityManagerInterface|MockObject $manager;
    private ConvertToDatabaseValueMock $convertToDatabaseValue;
    private SerializeTyped $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEntityManager();
        $this->convertToDatabaseValue = ConvertToDatabaseValueMock::fromEntityManager($this->manager);
    }

    public function testCanBeSerializedIfHasRegisteredType(): void
    {
        $this->setUpSerializer(
            [
                'property' => Type::getType(Types::DATETIMETZ_IMMUTABLE),
            ]
        );

        self::assertTrue($this->serializer->isPossible(new SerializationContext('property', '', [])));
    }

    public function testCantBeSerializedIfHasNoRegisteredType(): void
    {
        $this->setUpSerializer(
            [
                'another' => Type::getType(Types::JSON),
            ]
        );

        self::assertFalse($this->serializer->isPossible(new SerializationContext('property', '', [])));
    }

    public function testSerializeUsingType(): void
    {
        $type = Type::getType(Types::BOOLEAN);
        $this->setUpSerializer(['another' => $type]);

        $this->convertToDatabaseValue->will(
            self::returnValueMap(
                [
                    [$type, true, 'true']
                ]
            )
        );

        self::assertSame(
            'true',
            $this->serializer->__invoke(new SerializationContext('another', true, [])),
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
