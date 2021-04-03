<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Mocks\Types;

use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Types\TypeIsEmbedded;

final class TypeIsEmbeddedMock implements TypeIsEmbedded
{
    private ReturnValueMap $shouldReturn;

    public function __invoke(string $className): bool
    {
        return $this->shouldReturn->invoke(
            new Invocation(
                self::class,
                __FUNCTION__,
                [$className],
                'bool',
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
