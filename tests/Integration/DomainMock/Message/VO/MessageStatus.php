<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Message\VO;

use Webmozart\Assert\Assert;

final class MessageStatus
{
    private const STATUSES = [
        'NEW',
        'EDITED',
    ];

    private string $status;

    public function __construct(string $status)
    {
        Assert::inArray($status, self::STATUSES);
        $this->status = $status;
    }

    public static function NEW(): self
    {
        return new self(__FUNCTION__);
    }

    public static function EDITED(): self
    {
        return new self(__FUNCTION__);
    }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public function transitionInto(self $other): self
    {
        $this->assertCanBeTransitionedInto($other);

        return $other;
    }

    public function assertCanBeTransitionedInto(self $other): void
    {
        // todo
    }
}
