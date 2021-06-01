<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Message\Events;

use Tests\Integration\DomainMock\Message\Doctrine\Message;
use Tests\Integration\DomainMock\Message\VO\MessageContent;

class MessageWasEdited extends MessageEvent
{
    private bool $showWasEdited;

    private MessageContent $oldContent;

    private MessageContent $newContent;

    public function __construct(Message $entity, MessageContent $oldContent, MessageContent $newContent, bool $showWasEdited)
    {
        parent::__construct($entity);

        $this->oldContent = $oldContent;
        $this->newContent = $newContent;
        $this->showWasEdited = $showWasEdited;
    }

    public static function with(Message $message, MessageContent $oldContent, MessageContent $newContent, bool $showWasEdited): self
    {
        return new self($message, $oldContent, $newContent, $showWasEdited);
    }

    public function getOldContent(): MessageContent
    {
        return $this->oldContent;
    }

    public function getNewContent(): MessageContent
    {
        return $this->newContent;
    }

    public function showWasEdited(): bool
    {
        return $this->showWasEdited;
    }

    public static function NAME(): string
    {
        return 'message_edited';
    }
}
