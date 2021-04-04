<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity;

use Doctrine\Common\Proxy\AbstractProxyFactory as ProxyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use JetBrains\PhpStorm\Immutable;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateDeserializer;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToPHPValue;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Types\ResolvePrimaryType;

#[Immutable]
final class DeserializeEntity implements SeparateDeserializer
{
    public function __construct(
        private UnitOfWork $unitOfWork,
        private ProxyFactory $proxyFactory,
        private ConvertToPHPValue $convertToPHPValue,
        private ResolvePrimaryType $resolvePrimaryType,
    ) {
    }

    public static function from(EntityManagerInterface $manager): self
    {
        return new self(
            $manager->getUnitOfWork(),
            $manager->getProxyFactory(),
            ConvertToPHPValue\Impl::fromEntityManager($manager),
            new ResolvePrimaryType\Impl($manager),
        );
    }

    public function isPossible(DeserializationContext $context): bool
    {
        return is_subclass_of($context->getType(), AggregateRoot::class);
    }

    public function __invoke(DeserializationContext $context): AggregateRoot
    {
        /** @var string|AggregateRoot $type */
        $name = $context->getName();
        $type = $context->getType();
        $serialized = $context->getSerialized();

        $primary = $this->getPrimary($type, $serialized[$name]);
        $identifier = [$type::getPrimaryName() => $primary];

        return $this->unitOfWork->tryGetById($identifier, $type)
            ?: $this->proxyFactory->getProxy($type, $identifier);
    }

    protected function getPrimary(string $type, mixed $serialized)
    {
        $primaryType = ($this->resolvePrimaryType)($type);

        return ($this->convertToPHPValue)($primaryType, $serialized);
    }
}
