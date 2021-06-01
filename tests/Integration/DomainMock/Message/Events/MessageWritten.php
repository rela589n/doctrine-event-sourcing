<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Message\Events;

use Tests\Integration\DomainMock\Chat\Doctrine\Chat;
use Tests\Integration\DomainMock\Message\Doctrine\Message;
use Tests\Integration\DomainMock\Message\VO\MessageContent;
use Tests\Integration\DomainMock\User\Doctrine\User;

class MessageWritten extends MessageEvent
{
    private function __construct(
        Message $message,
        private User $user,
        private Chat $chat,
        private MessageContent $content,
    ) {
        parent::__construct($message);
    }

    public static function withData(Message $message, User $user, Chat $chat, MessageContent $content): self
    {
        return new self($message, $user, $chat, $content);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getChat(): Chat
    {
        return $this->chat;
    }

    public function getContent(): MessageContent
    {
        return $this->content;
    }

    public function getMessage(): Message
    {
        return $this->entity;
    }

    public static function NAME(): string
    {
        return 'message_written';
    }
}
