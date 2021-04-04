<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Chat\Events;

use JetBrains\PhpStorm\Immutable;
use Tests\Integration\DomainMock\Chat\Doctrine\Chat;
use Tests\Integration\DomainMock\User\Doctrine\User;

#[Immutable]
class UserJoinedChat extends ChatEvent
{
    public function __construct(private User $user, Chat $chat)
    {
        parent::__construct($chat);
    }

    public static function with(User $user, Chat $chat): self
    {
        return new self($user, $chat);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getChat(): Chat
    {
        return $this->entity;
    }

    public function NAME(): string
    {
        return 'user_joined_chat';
    }
}
