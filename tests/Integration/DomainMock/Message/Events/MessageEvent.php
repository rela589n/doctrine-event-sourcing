<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Message\Events;

use Rela589n\DoctrineEventSourcing\Event\AggregateChanged;
use Tests\Integration\DomainMock\Message\Doctrine\Message;

abstract class MessageEvent extends AggregateChanged
{
    public function __construct(Message $message)
    {
        parent::__construct($message);
    }
}
