<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event\Exceptions;

use LogicException;

final class UnexpectedAggregateChangeEvent extends LogicException
{
    private $event;

    public function __construct($event)
    {
        parent::__construct('Unexpected event: '.$event::class);
        $this->event = $event;
    }

    public function getEvent()
    {
        return $this->event;
    }
}
