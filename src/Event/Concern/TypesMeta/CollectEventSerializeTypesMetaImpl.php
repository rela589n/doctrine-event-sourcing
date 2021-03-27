<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event\Concern\TypesMeta;

use ReflectionProperty;
use Rela589n\DoctrineEventSourcing\Event\Annotations\SerializeAs;

final class CollectEventSerializeMetaImpl implements CollectEventSerializeMeta
{
    public function __invoke(ReflectionProperty...$properties): array
    {
        $metaInfo = array_map(
            static fn(ReflectionProperty $property) => [
                'property' => $property->getName(),
                'annotation' => ($property->getAttributes(SerializeAs::class)[0] ?? null)?->newInstance(),
            ],
            $properties,
        );

        $metaInfo = array_filter(
            $metaInfo,
            static fn(array $annotated) => $annotated['annotation'],
        );

        return array_column($metaInfo, 'annotation', 'property');
    }
}
