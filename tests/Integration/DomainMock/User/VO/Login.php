<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\User\VO;

use Tests\Integration\DomainMock\User\VO\Exceptions\LoginInvalidException;

final class Login
{
    private string $email;

    private function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new LoginInvalidException("Email \"$email\" is invalid.");
        }

        $this->email = $email;
    }

    public static function fromString(string $string): self
    {
        return new self($string);
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
