<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Chat\Doctrine;

use DateTimeInterface as DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface as Uuid;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Event\Exceptions\UnexpectedAggregateChangeEvent;
use Tests\Integration\DomainMock\Chat\Events\ChatCreated;
use Tests\Integration\DomainMock\Chat\Events\ChatEvent;
use Tests\Integration\DomainMock\Chat\Events\UserJoinedChat;
use Tests\Integration\DomainMock\Chat\VO\ChatName;
use Tests\Integration\DomainMock\Message\Doctrine\Message;
use Tests\Integration\DomainMock\User\Doctrine\User;

class Chat implements AggregateRoot
{
    private Uuid $uuid;

    private ChatName $name;

    private DateTime $createdAt;

    private DateTime $updatedAt;

    /** @var Collection<User> */
    private Collection $users;

    /** @var Collection<Message> */
    private Collection $messages;

    /** @var Collection<ChatEvent> */
    private Collection $recordedEvents;

    /** @var ChatEvent[] */
    private array $newlyRecordedEvents = [];

    private function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
        $this->users = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->recordedEvents = new ArrayCollection();
    }

    public static function create(Uuid $uuid, ChatName $name): self
    {
        $chat = new self($uuid);
        $chat->recordThat(ChatCreated::with($chat, $name));

        return $chat;
    }

    public function acceptUser(User $user): void
    {
        if ($this->users->contains($user)) {
            return;
        }

        $this->recordThat(UserJoinedChat::with($user, $this));
    }

    public function addMessage(Message $message): void
    {
        $this->messages->add($message);
    }

    private function recordThat(ChatEvent $event): void
    {
        switch (true) {
            case $event instanceof ChatCreated:
                $this->createdAt = $event->getTimestamp();
                $this->name = $event->getChatName();
                break;
            case $event instanceof UserJoinedChat:
                $user = $event->getUser();
                $this->users->add($user);
                $user->joinChat($this);
                break;
            default:
                throw new UnexpectedAggregateChangeEvent($event);
        }

        $this->updatedAt = $event->getTimestamp();
        $this->newlyRecordedEvents[] = $event;
        $this->recordedEvents->add($event);
    }

    public function releaseEvents(): array
    {
        $events = $this->newlyRecordedEvents;
        $this->newlyRecordedEvents = [];
        return $events;
    }

    public function getPrimary(): Uuid
    {
        return $this->uuid;
    }

    public static function getPrimaryName(): string
    {
        return 'uuid';
    }
}
