<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Chat\VO;

use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\Castable;
use Tests\Integration\DomainMock\Chat\Casts\ChatNameCast;

final class ChatName implements Castable
{
    private string $name;

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public static function castUsing(array $arguments): ChatNameCast
    {
        return new ChatNameCast();
    }
}
