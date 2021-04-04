<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event\Contract;

use DateTimeInterface as DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

interface AggregateChanged
{
    public function NAME(): string;

    public function getTimestamp(): DateTime;

    public function onPreFlushAggregateChanged(PreFlushEventArgs $args): void;

    public function onPostLoadAggregateChanged(LifecycleEventArgs $args): void;
}
