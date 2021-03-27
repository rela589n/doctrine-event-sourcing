<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Typed;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed\DeserializeTyped;
use Tests\Unit\Serializer\Mocks\Converter\ConvertToPHPValueMock;

final class TypedDeserializerTest extends TestCase
{
    private EntityManagerInterface|MockObject $manager;
    private ConvertToPHPValueMock $convertToPHPValue;
    private DeserializeTyped $deserializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEntityManager();
        $this->convertToPHPValue = ConvertToPHPValueMock::fromEntityManager($this->manager);
    }

    public function testCanBeDeserializedIfHasRegisteredType(): void
    {
        $this->setUpDeserializer(
            [
                'property' => Type::getType(Types::DATETIMETZ_IMMUTABLE),
            ]
        );

        self::assertTrue($this->deserializer->isPossible(new DeserializationContext('property', '', [])));
    }

    public function testCantBeDeserializedIfHasNoRegisteredType(): void
    {
        $this->setUpDeserializer(
            [
                'another' => Type::getType(Types::JSON),
            ]
        );

        self::assertFalse($this->deserializer->isPossible(new DeserializationContext('property', '', [])));
    }

    public function testDeserializeUsingType(): void
    {
        $type = Type::getType(Types::DATETIMETZ_MUTABLE);
        $this->setUpDeserializer(['another' => $type]);

        $this->convertToPHPValue->will(
            self::returnValueMap(
                [[$type, ['serialized'], 'serialization result']]
            )
        );

        self::assertSame(
            'serialization result',
            $this->deserializer->__invoke(
                new DeserializationContext('another', DateTime::class, ['another' => ['serialized']])
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

    private function setUpDeserializer(array $propertiesTypes): void
    {
        $this->deserializer = new DeserializeTyped(
            $this->convertToPHPValue,
            $propertiesTypes,
        );
    }
}
