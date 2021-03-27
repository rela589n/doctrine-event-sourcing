<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity;

use Doctrine\Common\Proxy\AbstractProxyFactory as ProxyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Serializer\Context\DeserializationContext;
use Rela589n\DoctrineEventSourcing\Serializer\Separate\SeparateDeserializer;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToPHPValue;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Types\ResolvePrimaryType;

final class DeserializeEntity implements SeparateDeserializer
{
    public function __construct(
        protected ProxyFactory $proxyFactory,
        protected ConvertToPHPValue $convertToPHPValue,
        protected ResolvePrimaryType $resolvePrimaryType,
    ) {
    }

    public static function from(EntityManagerInterface $manager): self
    {
        return new self(
            $manager->getProxyFactory(),
            ConvertToPHPValue::fromEntityManager($manager),
            new ResolvePrimaryType($manager),
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

        return $this->proxyFactory->getProxy(
            $type,
            [$type::getPrimaryName() => $this->getPrimary($type, $serialized[$name])],
        );
    }

    protected function getPrimary(string $type, mixed $serialized)
    {
        $primaryType = ($this->resolvePrimaryType)($type);

        return ($this->convertToPHPValue)($primaryType, $serialized);
    }
}
