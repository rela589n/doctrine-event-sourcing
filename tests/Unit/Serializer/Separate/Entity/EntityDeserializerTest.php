<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Entity;

use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\DeserializeEntity;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Types\ResolvePrimaryType;
use Tests\Mocks\AggregateRootMock;
use Tests\Unit\Serializer\Mocks\Converter\ConvertToPHPValueMock;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\DeserializeEntity
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext
 */
final class EntityDeserializerTest extends TestCase
{
    private MockObject|EntityManagerInterface $manager;
    private MockObject|AbstractProxyFactory $proxyFactory;
    private DeserializeEntity $deserializer;
    private ConvertToPHPValueMock $convertToPHPValue;
    private MockObject|ResolvePrimaryType $resolvePrimaryTypeMock;
    private MockObject|UnitOfWork $unitOfWork;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEntityManager();
        $this->setUpDeserializer();
    }

    public function testCanBeDeserializedIfItsEntity(): void
    {
        $entity = $this->createMock(AggregateRoot::class);
        self::assertTrue(
            $this->deserializer->isPossible(
                DeserializationContext::make()
                    ->withFieldName('')
                    ->withType($entity::class)
                    ->withSerialized([])
            )
        );
    }

    public function testCentBeDeserializedIfItsNotEntity(): void
    {
        $notEntity = $this->createMock(EntityManagerInterface::class);
        self::assertFalse(
            $this->deserializer->isPossible(
                DeserializationContext::make()
                    ->withFieldName('')
                    ->withType($notEntity::class)
                    ->withSerialized([])
            )
        );
    }

    public function testDeserializesEntityWithoutProxyIfExistsInIdentityMap(): void
    {
        $user = new AggregateRootMock('123e4567-e89b-12d3-a456-423614174001');
        $serialized = ['user' => $user->getPrimary()];

        $this->resolvePrimaryTypeMock
            ->method('__invoke')
            ->willReturnMap([[AggregateRootMock::class, Type::getType(Types::GUID)]]);

        $resolvedPrimary = Uuid::fromString($user->getPrimary());

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

        AggregateRootMock::setPrimaryName('uuid');

        $this->unitOfWork->method('tryGetById')
            ->willReturnMap(
                [
                    [[AggregateRootMock::getPrimaryName() => $resolvedPrimary], AggregateRootMock::class, $user],
                ]
            );

        $deserialized = ($this->deserializer)(
            DeserializationContext::make()
                ->withFieldName('user')
                ->withType(AggregateRootMock::class)
                ->withSerialized($serialized)
        );

        self::assertSame($user, $deserialized);
    }

    public function testDeserializeEntityUsingPrimary(): void
    {
        $primary = '123e4567-e89b-12d3-a456-423614174001';
        $serialized = ['user' => $primary];

        AggregateRootMock::setPrimaryName('uuid');

        $this->resolvePrimaryTypeMock
            ->method('__invoke')
            ->willReturnMap([[AggregateRootMock::class, Type::getType(Types::GUID)]]);

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
            ($this->deserializer)(
                DeserializationContext::make()
                    ->withFieldName('user')
                    ->withType(AggregateRootMock::class)
                    ->withSerialized($serialized)
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

    private function setUpDeserializer()
    {
        $this->convertToPHPValue = new ConvertToPHPValueMock();
        $this->proxyFactory = $this->createMock(AbstractProxyFactory::class);
        $this->resolvePrimaryTypeMock = $this->createMock(ResolvePrimaryType::class);
        $this->unitOfWork = $this->createMock(UnitOfWork::class);
        $this->deserializer = new DeserializeEntity(
            $this->unitOfWork,
            $this->proxyFactory,
            $this->convertToPHPValue,
            $this->resolvePrimaryTypeMock,
        );
    }
}
