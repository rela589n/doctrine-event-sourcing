<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\User\VO\Exceptions;

final class PasswordTooLongException extends \RuntimeException
{
    private string $sourcePassword;
    private int $maxLength;

    public function __construct(string $sourcePassword, int $maxLength)
    {
        parent::__construct('Password is too long!');

        $this->sourcePassword = $sourcePassword;
        $this->maxLength = $maxLength;
    }

    public function getSourcePassword(): string
    {
        return $this->sourcePassword;
    }

    public function getMaxLength(): int
    {
        return $this->maxLength;
    }
}
