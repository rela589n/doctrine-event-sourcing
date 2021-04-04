<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\SerializeEntity;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToDatabaseValue;
use Tests\Mocks\AggregateRootMock;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\SerializeEntity
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext
 */
final class EntitySerializerTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|ClassMetadata $classMetadata;
    private MockObject|ConvertToDatabaseValue $convertToDatabaseValue;
    private SerializeEntity $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSerializer();
    }

    public function testCanBeSerializedIfItsEntity(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        self::assertTrue(
            $this->serializer->isPossible(
                SerializationContext::make()
                    ->withFieldName('')
                    ->withValue($entity)
                    ->withAttributes([])
            )
        );
    }

    public function testCantBeSerializedIfItsNotEntity(): void
    {
        $notEntity = $this->createMock(EntityManagerInterface::class);
        self::assertFalse(
            $this->serializer->isPossible(
                SerializationContext::make()
                    ->withFieldName('')
                    ->withValue($notEntity)
                    ->withAttributes([])
            )
        );
    }

    public function testSerializesEntityUsingPrimary(): void
    {
        $primary = '123e4567-e89b-12d3-a456-426614174000';
        $entity = new AggregateRootMock($primary);
        AggregateRootMock::setPrimaryName('uuid');

        $this->classMetadata->method('getFieldMapping')
            ->willReturnMap([['uuid', ['type' => 'dbal_type']]]);

        $this->entityManager->method('getClassMetadata')
            ->willReturnMap([[AggregateRootMock::class, $this->classMetadata]]);

        $this->convertToDatabaseValue
            ->method('__invoke')
            ->willReturnMap([['dbal_type', $primary, 'serialized'.$primary]]);

        self::assertSame(
            'serialized'.$primary,
            ($this->serializer)(
                SerializationContext::make()
                    ->withFieldName('')
                    ->withValue($entity)
                    ->withAttributes([])
            )
        );
    }

    private function setUpSerializer(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $platform = $this->createMock(AbstractPlatform::class);

        $connection->method('getDatabasePlatform')
            ->willReturn($platform);

        $this->entityManager->method('getConnection')
            ->willReturn($connection);

        $this->classMetadata = $this->createMock(ClassMetadata::class);

        $this->convertToDatabaseValue = $this->createMock(ConvertToDatabaseValue::class);
        $this->serializer = new SerializeEntity($this->entityManager, $this->convertToDatabaseValue);
    }
}
