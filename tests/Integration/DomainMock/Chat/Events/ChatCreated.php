<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Chat\Events;

use Carbon\Carbon;
use Rela589n\DoctrineEventSourcing\Event\Annotations\SerializeAs;
use Tests\Integration\DomainMock\Chat\Doctrine\Chat;
use Tests\Integration\DomainMock\Chat\VO\ChatName;

class ChatCreated extends ChatEvent
{
    #[SerializeAs(type: 'carbondatetimetz', name: 'show_tutorial_at')]
    private Carbon $showTutorialAt;

    #[SerializeAs(name: 'chat_name')]
    private ChatName $chatName;

    public function __construct(Chat $chat, ChatName $name)
    {
        parent::__construct($chat);
        $this->chatName = $name;
        $this->showTutorialAt = Carbon::now()
            ->addSeconds(45);
    }

    public static function with(Chat $chat, ChatName $name): self
    {
        return new self($chat, $name);
    }

    public function getChatName(): ChatName
    {
        return $this->chatName;
    }

    public function getShowTutorialAt(): Carbon
    {
        return $this->showTutorialAt;
    }

    public function NAME(): string
    {
        return 'chat_created';
    }

    public function getChat(): Chat
    {
        return $this->entity;
    }
}
