<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Embedded;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded\SerializeEmbedded;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToDatabaseValue;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Types\TypeIsEmbedded;
use stdClass;
use Tests\Unit\Serializer\Separate\Embedded\Mocks\EmbeddedValueObject;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded\SerializeEmbedded
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\SerializationContext
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Util\Types\TypeIsEmbedded\Impl
 */
final class EmbeddedSerializerTest extends TestCase
{
    private MockObject|EntityManagerInterface $manager;
    private MockObject|TypeIsEmbedded $typeIsEmbedded;
    private MockObject|ConvertToDatabaseValue $convertToDatabaseValue;
    private SerializeEmbedded $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEntityManager();
        $this->setupMisc();
    }

    public function testCanBeSerializedIfTypeIsEmbedded(): void
    {
        $this->setUpSerializer($this->createMock(ClassMetadataInfo::class));
        $this->typeIsEmbedded
            ->method('__invoke')
            ->willReturnMap([[stdClass::class, true]]);

        $context = SerializationContext::make()
            ->withFieldName('')
            ->withValue(new stdClass())
            ->withAttributes([]);
        self::assertTrue($this->serializer->isPossible($context));
    }

    public function testCantBeSerializedIfTypeIsNotEmbedded(): void
    {
        $this->setUpSerializer($this->createMock(ClassMetadataInfo::class));
        $this->typeIsEmbedded
            ->method('__invoke')
            ->willReturnMap([[stdClass::class, false]]);

        $context = SerializationContext::make()
            ->withFieldName('')
            ->withValue(new stdClass())
            ->withAttributes([]);
        self::assertFalse($this->serializer->isPossible($context));
    }

    public function testCantBeSerializedIfTypeIsNotObject(): void
    {
        $this->setUpSerializer($this->createMock(ClassMetadataInfo::class));
        self::assertFalse(
            $this->serializer->isPossible(
                SerializationContext::make()
                    ->withFieldName('')
                    ->withValue('not an object')
                    ->withAttributes([])
            )
        );
    }

    public function testSerializeEmbedded(): void
    {
        $valueMeta = $this->createMock(ClassMetadataInfo::class);

        $reflFieldFirst = new ReflectionProperty(EmbeddedValueObject::class, 'property1');
        $reflFieldFirst->setAccessible(true);
        $valueMeta->reflFields['property1'] = $reflFieldFirst;

        $reflFieldSecond = new ReflectionProperty(EmbeddedValueObject::class, 'property2');
        $reflFieldSecond->setAccessible(true);
        $valueMeta->reflFields['property2'] = $reflFieldSecond;

        $this->manager->method('getClassMetadata')
            ->willReturnMap([[EmbeddedValueObject::class, $valueMeta]]);

        $entityMeta = $this->createMock(ClassMetadataInfo::class);
        $entityMeta->fieldMappings = [
            [
                'originalClass' => 'should be ignored',
            ],
            [
                'originalClass' => EmbeddedValueObject::class,
                'type' => 'string',
                'columnName' => 'p1',
                'originalField' => 'property1',
            ],
            [
                'originalClass' => EmbeddedValueObject::class,
                'type' => 'string',
                'columnName' => 'p2',
                'originalField' => 'property2',
            ],
        ];

        $this->setUpSerializer($entityMeta);

        $valueObject = new EmbeddedValueObject('first value', 'second value');

        $this->convertToDatabaseValue
            ->method('__invoke')
            ->willReturnMap(
                [
                    ['string', $valueObject->getProperty1(), 'first serialized'],
                    ['string', $valueObject->getProperty2(), 'second serialized'],
                ]
            );

        $serialized = $this->serializer->__invoke(
            SerializationContext::make()
                ->withFieldName('property')
                ->withValue($valueObject)
                ->withAttributes([])
        );
        self::assertIsArray($serialized);

        self::assertSame(
            [
                'p1' => 'first serialized',
                'p2' => 'second serialized',
            ],
            $serialized,
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

    private function setupMisc(): void
    {
        $this->typeIsEmbedded = $this->createMock(TypeIsEmbedded::class);
        $this->convertToDatabaseValue = $this->createMock(ConvertToDatabaseValue::class);
    }

    private function setUpSerializer(ClassMetadataInfo $entityMeta): void
    {
        $this->serializer = new SerializeEmbedded(
            $this->manager,
            $entityMeta,
            $this->typeIsEmbedded,
            $this->convertToDatabaseValue,
        );
    }
}
