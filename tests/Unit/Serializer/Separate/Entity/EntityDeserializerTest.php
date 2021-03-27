<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Entity;

use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\DeserializeEntity;
use Tests\Mocks\AggregateRootMock;
use Tests\Unit\Serializer\Mocks\Converter\ConvertToPHPValueMock;
use Tests\Unit\Serializer\Mocks\Types\ResolvePrimaryTypeMock;

final class EntityDeserializerTest extends TestCase
{
    private MockObject|EntityManagerInterface $manager;
    private MockObject|AbstractProxyFactory $proxyFactory;
    private DeserializeEntity $deserializer;
    private ConvertToPHPValueMock $convertToPHPValue;
    private ResolvePrimaryTypeMock $resolvePrimaryTypeMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEntityManager();
        $this->setUpDeserializer();
    }

    public function testCanBeDeserializedIfItsEntity(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        self::assertTrue($this->deserializer->isPossible(new DeserializationContext('', $entity::class, [])));
    }

    public function testCentBeDeserializedIfItsNotEntity(): void
    {
        $notEntity = $this->createMock(EntityManagerInterface::class);
        self::assertFalse($this->deserializer->isPossible(new DeserializationContext('', $notEntity::class, [])));
    }

    public function testDeserializeEntityUsingPrimary(): void
    {
        $primary = '123e4567-e89b-12d3-a456-423614174001';
        $serialized = ['user' => $primary];

        AggregateRootMock::setPrimaryName('uuid');

        $this->resolvePrimaryTypeMock
            ->will(
                self::returnValueMap(
                    [
                        [AggregateRootMock::class, Type::getType(Types::GUID)]
                    ]
                )
            );

        $resolvedPrimary = Uuid::fromString($primary);
        $this->convertToPHPValue
            ->will(
                self::returnValueMap(
                    [
                        [
                            Type::getType(Types::GUID),
                            $serialized['user'],
                            $resolvedPrimary,
                        ],
                    ]
                )
            );

        $user = new AggregateRootMock($primary);

        $this->proxyFactory->method('getProxy')
                           ->willReturnMap(
                               [
                                   [AggregateRootMock::class, ['uuid' => $resolvedPrimary], $user]
                               ]
                           );

        self::assertSame(
            $user,
            ($this->deserializer)(new DeserializationContext('user', AggregateRootMock::class, $serialized)),
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

    private function setUpDeserializer()
    {
        $this->convertToPHPValue = ConvertToPHPValueMock::fromEntityManager($this->manager);
        $this->proxyFactory = $this->createMock(AbstractProxyFactory::class);
        $this->resolvePrimaryTypeMock = new ResolvePrimaryTypeMock($this->manager);
        $this->deserializer = new DeserializeEntity(
            $this->proxyFactory,
            $this->convertToPHPValue,
            $this->resolvePrimaryTypeMock,
        );
    }
}
