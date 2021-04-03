<?php

declare(strict_types=1);

namespace Tests\Unit\Serializer\Mocks\Converter;

use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap;
use Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToPHPValue;

final class ConvertToPHPValueMock implements ConvertToPHPValue
{
    private ReturnValueMap $shouldReturn;

    public function __invoke(string|Type $type, mixed $value): mixed
    {
        return $this->shouldReturn->invoke(
            new Invocation(
                self::class,
                __FUNCTION__,
                [$type, $value],
                'mixed',
                $this,
            ),
        );
    }

    public function will(ReturnValueMap $map): self
    {
        $this->shouldReturn = $map;
        return $this;
    }
}
