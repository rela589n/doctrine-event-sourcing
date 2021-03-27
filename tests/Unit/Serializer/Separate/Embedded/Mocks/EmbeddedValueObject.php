<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Separate\Embedded\Mocks;

final class EmbeddedValueObject
{
    public function __construct(private $property1, private $property2) { }

    public function getProperty1()
    {
        return $this->property1;
    }

    public function getProperty2()
    {
        return $this->property2;
    }
}
