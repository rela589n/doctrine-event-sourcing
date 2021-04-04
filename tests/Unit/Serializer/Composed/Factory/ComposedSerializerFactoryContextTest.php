<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Composed\Factory;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer\Factory\Context;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer\Factory\Context
 */
final class ComposedSerializerFactoryContextTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|AggregateRoot $entity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entity = $this->createMock(AggregateRoot::class);
    }

    public function testHoldsGivenData(): void
    {
        $context = Context::make()
            ->withEntityManager($this->entityManager)
            ->withEntity($this->entity)
            ->withPropertiesMeta(['properties' => 'meta'])
            ->withCastArgumentsMap(['cast' => 'map']);

        self::assertSame($this->entityManager, $context->getManager());
        self::assertSame($this->entity, $context->getEntity());
        self::assertSame(['properties' => 'meta'], $context->getPropertiesMeta());
        self::assertSame(['cast' => 'map'], $context->getCastArgumentsMap());
    }
}
