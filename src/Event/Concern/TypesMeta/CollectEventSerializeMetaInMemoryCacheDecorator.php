<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event\Concern\TypesMeta;

use ReflectionProperty;

final class CollectEventSerializeMetaInMemoryCacheDecorator implements CollectEventSerializeMeta
{
    private array $storage = [];

    public function __construct(private CollectEventSerializeMeta $decorated) { }

    public function __invoke(ReflectionProperty ...$properties): array
    {
        $hash = $this->calculateHash(...$properties);

        return $this->storage[$hash]
            ?? $this->storage[$hash] = $this->decorated->__invoke(...$properties);
    }

    private function calculateHash(ReflectionProperty ...$properties): int
    {
        $hash = 0;
        $i = 1;

        foreach ($properties as $property) {
            $hash += $i * spl_object_id($property);
            $i = ($i * 31) % (1 << 32);
        }

        return $hash;
    }
}
