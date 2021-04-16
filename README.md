# Doctrine Event Sourcing

[![codecov](https://codecov.io/gh/rela589n/doctrine-event-sourcing/branch/master/graph/badge.svg?token=KYYX649NPW)](https://codecov.io/gh/rela589n/doctrine-event-sourcing)

This package is intended to simplify implementation of Event Sourcing pattern in applications using Doctrine ORM.

## Installation

You can install the package via composer:

```shell
composer require rela589n/doctrine-event-sourcing
```

Configure `vendor/rela589n/doctrine-event-sourcing/config/mappings/` as doctrine mappings directory with the higher
priority than your mappings.

## Getting Started

### Introduce the event sourcing into domain model

Implement `\Rela589n\DoctrineEventSourcing\Entity\AggregateRoot` with your entity.

```php
use Ramsey\Uuid\UuidInterface as Uuid;
use Rela589n\DoctrineEventSourcing\Entity\AggregateRoot;

class User implements AggregateRoot 
{
    private Uuid $uuid;

    public static function getPrimaryName(): string
    {
        return 'uuid';
    }

    public function getPrimary(): Uuid
    {
        return $this->uuid;
    }
}
```

Create Base Event class for your entity.

```php
use Rela589n\DoctrineEventSourcing\Event\AggregateChanged;

abstract class UserEvent extends AggregateChanged
{
    public function __construct(User $user)
    {
        parent::__construct(entity: $user);
    }
}
```

Abstract event class for each entity is useful because we can use it's type-hint later when applying events.

> If you don't like inheritance in general or can't inherit from `AggregateChanged` abstract class, you can simply implement interface `Contract\AggregateChanged` and use trait `Concern\AggregateChanged` as implementation. But keep in mind that in such case you would have to define your own configuration mapping similar to `Rela589n.DoctrineEventSourcing.Event.AggregateChanged.dcm.xml`. It is because `mapped-superclass` doesn't work with indirect interfaces.

Now, lets create event, which represents something happening in our application.

```php
class UserRegistered extends UserEvent
{
    private Login $login;
    private Password $password;
    private UserName $userName;

    private function __construct(User $user, Login $login, Password $password, UserName $name)
    {
        parent::__construct($user);
        $this->login = $login;
        $this->password = $password;
        $this->userName = $name;
    }

    public static function withCredentials(User $user, Login $login, Password $password, UserName $name): self
    {
        return new self($user, $login, $password, $name);
    }

   // bunch of getters here

    public function NAME(): string
    {
        return 'user_registered';
    }
}
```

You may wonder what are `Login`, `Password`, `UserName` are all about. It all are value objects. Usually you map them to
your entity as [embeddables](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/tutorials/embeddables.html).

> Although Events are by nature entities, you don't need to worry about relations or identifiers. It is all done for you.

Once you have an events, you can implement your business logic with Event-Driven principle.

```php
use Rela589n\DoctrineEventSourcing\Event\Exceptions\UnexpectedAggregateChangeEvent;

class User implements AggregateRoot
{
    private Uuid $uuid;

    private Login $login;

    private Password $password;

    private UserName $name;

    private DateTimeInterface $createdAt;

    private DateTimeInterface $updatedAt;

    /** @var Collection<UserEvent> */
    private Collection $recordedEvents;

    /** @var UserEvent[] */
    private array $newlyRecordedEvents = [];

    private function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
        $this->recordedEvents = new ArrayCollection();
    }

    public static function register(Uuid $uuid, Login $login, Password $password, UserName $name): self
    {
        $user = new self($uuid);

        $user->recordThat(
            UserRegistered::withCredentials($user, $login, $password, $name)
        );

        return $user;
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
```

### When your domain entity is ready and events are driving the domain, it's time to think about persistence.

First off, let's define mapping for Abstract `UserEvent` class. We use `xml` just for example:

```xml

<entity name="App\Models\User\Events\UserEvent" table="user_events" inheritance-type="SINGLE_TABLE">
    <many-to-one field="entity" target-entity="App\Models\User\Doctrine\User" inversed-by="recordedEvents">
        <join-column name="user_uuid" referenced-column-name="uuid"/>
    </many-to-one>

    <discriminator-column name="name" type="string"/>

    <discriminator-map>
        <discriminator-mapping value="user_changed_login" class="App\Models\User\Events\UserChangedLogin"/>
        <discriminator-mapping value="user_registered" class="App\Models\User\Events\UserRegistered"/>
    </discriminator-map>
</entity>
```

> Main part here is that `<discriminator-map>` part, because it tells Doctrine how to hydrate data into correct event objects.

As well wee need annotate all final event classes as entities.

```xml

<entity name="App\Models\User\Events\UserRegistered">
</entity>
```

As you can see, final event classes don't need any annotations except an `@Entity`. This is because all payload fields
are mapped into jsonb for you.

> For final event classes describing with xml may be tedious, so feel free to use annotations.

The last one step we should do is map `recordedEvents` and value objects for entity. Each entity using event driven
approach should do this.

```xml

<entity name="App\Models\User\Doctrine\User" table="users">
    <!--  actually, doctrine doesn't have uuid type, this one is from third-party library  -->
    <id name="uuid" type="uuid" column="uuid">
        <generator strategy="NONE"/>
    </id>

    <embedded name="login" class="App\Models\User\VO\Login" use-column-prefix="false"/>
    <embedded name="password" class="App\Models\User\VO\Password" use-column-prefix="false"/>
    <embedded name="name" class="App\Models\User\VO\UserName" use-column-prefix="false"/>

    <field name="createdAt" type="datetimetz" column="created_at"/>
    <field name="updatedAt" type="datetimetz" column="updated_at"/>

    <!--  other fields and relations  -->

    <one-to-many field="recordedEvents" target-entity="App\Models\User\Events\UserEvent" mapped-by="entity">
        <cascade>
            <cascade-persist/>
        </cascade>
    </one-to-many>
</entity>
```

As well here we `embed` value objects such as `Login`, `Password`, `UserName`. What mapping for value object looks like:

```xml

<embeddable name="App\Models\User\VO\Password">
    <field name="passwordHash" column="password"/>
</embeddable>
```

### Check it out

```php
$email = 'johndoe'.Str::random().'@example.com';

$user = User::register(
    Uuid::uuid4(),
    Login::fromString($email),
    Password::fromRaw('hello world'),
    UserName::fromString('John Doe'),
);

$this->entityManager->persist($user);
$this->entityManager->flush();
```

Now, if we look into users table, everything is pretty obvious:

| uuid             | name     | email                    | password          | created_at          | updated_at          |
|------------------|----------|--------------------------|-------------------|---------------------|---------------------|
| 772bacea-9074... | John Doe | johndoeLEsqn@example.com | $2y$04$SKMHNCZ... | 2021-03-27 20:12:48 | 2021-03-27 20:12:48 |

What's more interesting is `user_events` table:

| id | name            | user_uuid        | payload                                                                                                                           | timestamp           |
|----|-----------------|------------------|-----------------------------------------------------------------------------------------------------------------------------------|---------------------|
| 20 | user_registered | 772bacea-9074... | {"login": {"email": "johndoeLEsqn@example.com"}, "password": {"password": "$2y$04$SKMHNCZ..."}, "userName": {"name": "John Doe"}} | 2021-03-27 20:12:48 |

### Let's get a bit crazy

Imagine messenger application, where we have Users, Chats, Messages.

```php
$message = Message::write(
    Uuid::uuid4(),
    $user,
    $chat,
    MessageContent::fromString('Some message')
);
```

What event would it trigger? Something like `MessageWritten` event:

```php
class MessageWritten extends MessageEvent
{
    private function __construct(
        Message $message,
        private User $user,
        private Chat $chat,
        private MessageContent $content,
    ) {
        parent::__construct($message);
    }

    public static function withData(Message $message, User $user, Chat $chat, MessageContent $content): self
    {
        return new self($message, $user, $chat, $content);
    }

    // bunch of getters

    public function NAME(): string
    {
        return 'message_written';
    }
}
```

You may wonder how it would get saved into database?

In messages table everything looks pretty boring:

| uuid             | status   | content      | user_uuid        | chat_uuid        | created_at          | updated_at          |
|------------------|----------|--------------|------------------|------------------|---------------------|---------------------|
| 3950eef0-ac89... | NEW      | Some message | 69ab80be-b05e... | b84a6b78-a173... | 2021-03-27 20:31:09 | 2021-03-27 20:31:09 |

What regards table `messsage_events`, it is full of magic:

| id | name            | message_uuid      | payload                                                                                          | timestamp           |
|----|-----------------|-------------------|--------------------------------------------------------------------------------------------------|---------------------|
| 14 | message_written | 3950eef0-ac89...  | {"chat": "b84a6b78-a173...", "user": "69ab80be-b05e...", "content": {"content": "Some message"}} | 2021-03-27 20:31:09 |

Payload fields `chat` and `user` were populated with primary keys. At the time events are loaded back, these entities
won't be loaded right away, but rather proxied by doctrine. If we really would like to deal with these related objects, 
doctrine will gracefully load them from the database to be used.

## Customization

### Customizing field names

By default, keys in json payload are fields names. You are free to customize this:

```php
#[SerializeAs(name: 'chat_name')]
private ChatName $chatName;
```

### Customizing DBAL type

```php
#[SerializeAs(type: Types::BOOLEAN)]
private int $arbitraryValue;

#[SerializeAs(type: 'carbondatetimetz', name: 'publish_at')]
private Carbon $someDate;
```

### Customizing Value Objects

By default, for VO persistence, owning entity embedded metadata is analysed and if Value Object has been mapped on
entity, it's mapping is used for persistence in events table.

To override this with your own logic, you may implement `Castable` interface with your value object. This interface
declares one method `castUsing`, which should return `CastsAttributes` object.

```php
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\Castable;
final class MessageContent implements Castable
{
    // main body
    
    public static function castUsing(array $arguments): MessageContentCast
    {
        return new MessageContentCast();
    }
}
```

Logic for casting value object:

```php
use Rela589n\DoctrineEventSourcing\Serializer\Separate\Castable\Contract\CastsAttributes;

final class MessageContentCast implements CastsAttributesEloquent, CastsAttributes
{
    public function get($model, $key, $value, $attributes): MessageContent
    {
        Assert::isInstanceOfAny($model, [EloquentMessage::class, DoctrineMessage::class]);

        return MessageContent::fromString($attributes['content']);
    }

    public function set($model, $key, $value, $attributes): array
    {
        Assert::isInstanceOfAny($model, [EloquentMessage::class, DoctrineMessage::class]);
        Assert::isInstanceOf($value, MessageContent::class);

        return [
            'content' => (string)$value,
        ];
    }
}
```

As you may have noticed, these interfaces are completely compatible with `Eloquent` ones so that you may reuse Cast for
both model and entity.

> If none of `Typed`, `Entity`,  `Embedded`, `Castable` strategies matched value, `Noop` strategy will be used.
> This means value will be given as it is when serializing and received as it is when deserializing.

