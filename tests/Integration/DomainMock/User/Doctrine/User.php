<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\User\Doctrine;

use DateTimeInterface as DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface as Uuid;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;
use Rela589n\DoctrineEventSourcing\Event\Exceptions\UnexpectedAggregateChangeEvent;
use Tests\Integration\DomainMock\Chat\Doctrine\Chat;
use Tests\Integration\DomainMock\User\Events\UserChangedLogin;
use Tests\Integration\DomainMock\User\Events\UserEvent;
use Tests\Integration\DomainMock\User\Events\UserRegistered;
use Tests\Integration\DomainMock\User\VO\Login;
use Tests\Integration\DomainMock\User\VO\Password;
use Tests\Integration\DomainMock\User\VO\UserName;

class User implements AggregateRoot
{
    private Uuid $uuid;

    private Login $login;

    private Password $password;

    private UserName $name;

    private DateTime $createdAt;

    private DateTime $updatedAt;

    /** @var Collection<Chat> */
    private Collection $chats;

    /** @var Collection<UserEvent> */
    private Collection $recordedEvents;

    /** @var UserEvent[] */
    private array $newlyRecordedEvents = [];

    private function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
        $this->chats = new ArrayCollection();
        $this->recordedEvents = new ArrayCollection();
    }

    public static function register(Uuid $uuid, Login $login, Password $password, UserName $name): self
    {
        $user = new self($uuid);

        $user->recordThat(UserRegistered::withCredentials($user, $login, $password, $name));

        return $user;
    }

    public function changeLogin(Login $newLogin): void
    {
        $this->recordThat(
            UserChangedLogin::fromInto($this, $this->login, $newLogin)
        );
    }

    public function joinChat(Chat $chat): void
    {
        if ($this->chats->contains($chat)) {
            return;
        }

        $this->chats->add($chat);
        $chat->acceptUser($this);
    }

    private function recordThat(UserEvent $event): void
    {
        switch (true) {
            case $event instanceof UserRegistered:
                $this->createdAt = $event->getTimestamp();
                $this->login = $event->getLogin();
                $this->password = $event->getPassword();
                $this->name = $event->getUserName();
                break;
            case $event instanceof UserChangedLogin:
                $this->login = $event->getNewLogin();
                break;
            default:
                throw new UnexpectedAggregateChangeEvent($event);
        }

        $this->updatedAt = $event->getTimestamp();
        $this->newlyRecordedEvents[] = $event;
        $this->recordedEvents->add($event);
    }

    public static function getPrimaryName(): string
    {
        return 'uuid';
    }

    public function getPrimary(): Uuid
    {
        return $this->uuid;
    }

    public function releaseEvents(): array
    {
        $events = $this->newlyRecordedEvents;
        $this->newlyRecordedEvents = [];
        return $events;
    }
}
