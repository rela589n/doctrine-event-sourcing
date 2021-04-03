<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Mocks\Types;

use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Types\ResolvePrimaryType;

final class ResolvePrimaryTypeMock implements ResolvePrimaryType
{
    private ReturnValueMap $shouldReturn;

    public function __invoke(string $className): Type
    {
        return $this->shouldReturn->invoke(
            new Invocation(
                self::class,
                __FUNCTION__,
                [$className],
                Type::class,
                $this,
            )
        );
    }

    public function will(ReturnValueMap $map): self
    {
        $this->shouldReturn = $map;
        return $this;
    }
}
