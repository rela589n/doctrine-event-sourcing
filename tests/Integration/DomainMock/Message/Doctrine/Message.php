<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Message\Doctrine;

use DateTimeInterface as DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface as Uuid;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Event\Exceptions\UnexpectedAggregateChangeEvent;
use Tests\Integration\DomainMock\Chat\Doctrine\Chat;
use Tests\Integration\DomainMock\Message\Events\MessageEvent;
use Tests\Integration\DomainMock\Message\Events\MessageWasEdited;
use Tests\Integration\DomainMock\Message\Events\MessageWritten;
use Tests\Integration\DomainMock\Message\VO\MessageContent;
use Tests\Integration\DomainMock\Message\VO\MessageStatus;
use Tests\Integration\DomainMock\User\Doctrine\User;

class Message implements AggregateRoot
{
    private Uuid $uuid;

    private MessageContent $content;

    private MessageStatus $status;

    private DateTime $createdAt;

    private DateTime $updatedAt;

    private User $user;

    private Chat $chat;

    /** @var Collection<MessageEvent> */
    private Collection $recordedEvents;

    /** @var MessageEvent[] */
    private array $newlyRecordedEvents = [];

    private function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
        $this->status = MessageStatus::NEW();
        $this->recordedEvents = new ArrayCollection();
    }

    public static function write(Uuid $uuid, User $user, Chat $chat, MessageContent $content): self
    {
        $message = new self($uuid);
        $message->recordThat(
            MessageWritten::withData($message, $user, $chat, $content),
        );

        return $message;
    }

    public function edit(MessageContent $newContent, bool $showWasEdited): void
    {
        $this->recordThat(
            MessageWasEdited::with($this, $this->content, $newContent, $showWasEdited),
        );
    }

    private function recordThat(MessageEvent $event): void
    {
        switch (true) {
            case $event instanceof MessageWritten:
                $this->createdAt = $event->getTimestamp();
                $this->user = $event->getUser();
                $this->chat = $event->getChat();
                $this->content = $event->getContent();
                $this->chat->addMessage($this);
                break;
            case $event instanceof MessageWasEdited:
                $this->content = $event->getNewContent();
                $this->status = $this->status->transitionInto(MessageStatus::EDITED());
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

    /**
     * This method provides access to private properties for testing.
     * In real application you'd never have such method.
     */
    public function _test_prop(string $name)
    {
        return $this->{$name};
    }
}
