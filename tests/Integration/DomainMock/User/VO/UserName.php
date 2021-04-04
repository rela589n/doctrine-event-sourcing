<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\User\VO;

final class UserName
{
    private string $name;

    public function __construct(string $name)
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
}
