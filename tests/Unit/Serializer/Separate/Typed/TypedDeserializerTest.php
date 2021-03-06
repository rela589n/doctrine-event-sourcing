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
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToPHPValue;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed\DeserializeTyped
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext
 */
final class TypedDeserializerTest extends TestCase
{
    private MockObject|EntityManagerInterface $manager;
    private MockObject|ConvertToPHPValue $convertToPHPValue;
    private DeserializeTyped $deserializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEntityManager();
        $this->convertToPHPValue = $this->createMock(ConvertToPHPValue::class);
    }

    public function testCanBeDeserializedIfHasRegisteredType(): void
    {
        $this->setUpDeserializer(
            [
                'property' => Type::getType(Types::DATETIMETZ_IMMUTABLE),
            ]
        );

        self::assertTrue(
            $this->deserializer->isPossible(
                DeserializationContext::make()
                    ->withFieldName('property')
            )
        );
    }

    public function testCantBeDeserializedIfHasNoRegisteredType(): void
    {
        $this->setUpDeserializer(
            [
                'another' => Type::getType(Types::JSON),
            ]
        );

        self::assertFalse(
            $this->deserializer->isPossible(
                DeserializationContext::make()
                    ->withFieldName('property')
            )
        );
    }

    public function testDeserializeUsingType(): void
    {
        $type = Type::getType(Types::DATETIMETZ_MUTABLE);
        $this->setUpDeserializer(['another' => $type]);

        $this->convertToPHPValue
            ->method('__invoke')
            ->willReturnMap([[$type, ['serialized'], 'serialization result']]);

        self::assertSame(
            'serialization result',
            $this->deserializer->__invoke(
                DeserializationContext::make()
                    ->withFieldName('another')
                    ->withType(DateTime::class)
                    ->withSerialized(['another' => ['serialized']])
            ),
        );
    }

    private function setupEntityManager(): void
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
