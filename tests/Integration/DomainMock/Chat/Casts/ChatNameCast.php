<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\Chat\Casts;

use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\CastsAttributes;
use Tests\Integration\DomainMock\Chat\Doctrine\Chat as DoctrineChat;
use Tests\Integration\DomainMock\Chat\VO\ChatName;
use Webmozart\Assert\Assert;

class ChatNameCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes): ChatName
    {
        Assert::isInstanceOfAny($model, [DoctrineChat::class]);

        return ChatName::fromString(
            $attributes['name'],
        );
    }

    public function set($model, $key, $value, $attributes): array
    {
        Assert::isInstanceOfAny($model, [DoctrineChat::class]);
        Assert::isInstanceOf($value, ChatName::class);

        return [
            'name' => (string)$value,
        ];
    }
}
