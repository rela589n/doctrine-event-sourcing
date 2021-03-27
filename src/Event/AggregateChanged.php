<?php

declare(strict_types=1);

namespace Rela589n\DoctrineEventSourcing\Event;

abstract class AggregateChanged implements Contract\AggregateChanged
{
    use Concern\AggregateChanged;
}
