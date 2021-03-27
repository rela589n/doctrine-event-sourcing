<?php

namespace Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract;

use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;

interface CastsAttributes
{
    /**
     * Transform the attribute from the underlying entity values.
     *
     * @param AggregateRoot $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes);

    /**
     * Transform the attribute to its underlying entity values.
     *
     * @param AggregateRoot $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return array
     */
    public function set($model, string $key, $value, array $attributes);
}
