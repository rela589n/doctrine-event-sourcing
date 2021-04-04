<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Chat\Events;

use Rela589n\DoctrineEventSourcing\Event\AggregateChanged;
use Tests\Integration\DomainMock\Chat\Doctrine\Chat;

abstract class ChatEvent extends AggregateChanged
{
    public function __construct(Chat $chat)
    {
        parent::__construct($chat);
    }
}
