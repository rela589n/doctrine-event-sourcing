<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\User\VO\Exceptions;

final class PasswordTooShortException extends \RuntimeException
{
    private string $sourcePassword;
    private int $minLength;

    public function __construct(string $sourcePassword, int $minLength)
    {
        parent::__construct('Password is too short!');

        $this->sourcePassword = $sourcePassword;
        $this->minLength = $minLength;
    }

    public function getSourcePassword(): string
    {
        return $this->sourcePassword;
    }

    public function getMinLength(): int
    {
        return $this->minLength;
    }
}
