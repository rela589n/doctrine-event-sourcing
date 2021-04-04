<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\User\Events;

use Rela589n\DoctrineEventSourcing\Event\AggregateChanged;
use Tests\Integration\DomainMock\User\Doctrine\User;

abstract class UserEvent extends AggregateChanged
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }
}
