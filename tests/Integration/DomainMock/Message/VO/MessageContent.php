<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Message\VO;

final class MessageContent
{
    private string $content;

    private function __construct(string $content)
    {
        $this->content = $content;
    }

    public static function fromString(string $content): self
    {
        return new self($content);
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
