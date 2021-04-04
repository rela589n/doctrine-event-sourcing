<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Embedded;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded\DeserializeEmbedded;
use stdClass;
use Tests\Unit\Serializer\Mocks\Converter\ConvertToPHPValueMock;
use Tests\Unit\Serializer\Mocks\Types\TypeIsEmbeddedMock;
use Tests\Unit\Serializer\Separate\Embedded\Mocks\EmbeddedValueObject;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded\DeserializeEmbedded
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext
 * @uses   \Rela589n\DoctrineEventSourcing\Serializer\Util\Types\TypeIsEmbedded\Impl
 */
final class EmbeddedDeserializerTest extends TestCase
{
    private EntityManagerInterface|MockObject $manager;
    private TypeIsEmbeddedMock $typeIsEmbedded;
    private ConvertToPHPValueMock $convertToPHPValue;
    private DeserializeEmbedded $deserializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEntityManager();
        $this->setupMisc();
    }

    public function testCanBeDeserializedIfTypeIsEmbedded(): void
    {
        $this->typeIsEmbedded->will(self::returnValueMap([[stdClass::class, true]]));

        $this->setupDeserializer($this->createMock(ClassMetadataInfo::class));
        self::assertTrue(
            $this->deserializer->isPossible(
                DeserializationContext::make()
                    ->withFieldName('')
                    ->withType(stdClass::class)
                    ->withSerialized([])
            )
        );
    }

    public function testCantBeDeserializedIfTypeIsNotEmbedded(): void
    {
        $this->typeIsEmbedded->will(self::returnValueMap([[stdClass::class, false]]));

        $this->setupDeserializer($this->createMock(ClassMetadataInfo::class));
        self::assertFalse(
            $this->deserializer->isPossible(
                DeserializationContext::make()
                    ->withFieldName('')
                    ->withType(stdClass::class)
                    ->withSerialized(
                        []
                    )
            )
        );
    }

    public function testDeserializeEmbedded(): void
    {
        $valueMeta = $this->createMock(ClassMetadataInfo::class);

        $reflFieldMockFirst = new ReflectionProperty(EmbeddedValueObject::class, 'property1');
        $reflFieldMockFirst->setAccessible(true);
        $valueMeta->reflFields['property1'] = $reflFieldMockFirst;

        $reflFieldMockSecond = new ReflectionProperty(EmbeddedValueObject::class, 'property2');
        $reflFieldMockSecond->setAccessible(true);
        $valueMeta->reflFields['property2'] = $reflFieldMockSecond;

        $valueMeta->reflClass = new ReflectionClass(EmbeddedValueObject::class);

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

        $this->setupDeserializer($entityMeta);

        $this->convertToPHPValue
            ->will(
                self::returnValueMap(
                    [
                        ['string', 'first serialized', 'first value'],
                        ['string', 'second serialized', 'second value'],
                    ],
                ),
            );

        /** @var EmbeddedValueObject $original */
        $original = $this->deserializer->__invoke(
            DeserializationContext::make()
                ->withFieldName('name')
                ->withType(EmbeddedValueObject::class)
                ->withSerialized(
                    [
                        'name' => [
                            'p1' => 'first serialized',
                            'p2' => 'second serialized',
                        ],
                        'whatever' => ['else'],
                    ],
                ),
        );

        self::assertSame('first value', $original->getProperty1());
        self::assertSame('second value', $original->getProperty2());
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
        $this->typeIsEmbedded = new TypeIsEmbeddedMock();
        $this->convertToPHPValue = new ConvertToPHPValueMock();
    }

    private function setupDeserializer(ClassMetadataInfo $entityMetadata): void
    {
        $this->deserializer = new DeserializeEmbedded(
            $this->manager,
            $entityMetadata,
            $this->typeIsEmbedded,
            $this->convertToPHPValue,
        );
    }
}
