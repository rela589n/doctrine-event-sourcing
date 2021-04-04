<?php

declare(strict_types=1);

namespace Tests\Integration\Event;

use Carbon\Carbon;
use Doctrine\Persistence\ObjectRepository;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Uuid;
use Tests\Integration\DomainMock\Chat\Doctrine\Chat;
use Tests\Integration\DomainMock\Chat\Events\ChatCreated;
use Tests\Integration\DomainMock\Chat\Events\ChatEvent;
use Tests\Integration\DomainMock\Chat\VO\ChatName;
use Tests\Integration\DomainMock\Message\Doctrine\Message;
use Tests\Integration\DomainMock\Message\Events\MessageEvent;
use Tests\Integration\DomainMock\Message\Events\MessageWasEdited;
use Tests\Integration\DomainMock\Message\Events\MessageWritten;
use Tests\Integration\DomainMock\Message\VO\MessageContent;
use Tests\Integration\DomainMock\User\Doctrine\User;
use Tests\Integration\DomainMock\User\Events\UserChangedLogin;
use Tests\Integration\DomainMock\User\Events\UserEvent;
use Tests\Integration\DomainMock\User\Events\UserRegistered;
use Tests\Integration\DomainMock\User\VO\Login;
use Tests\Integration\DomainMock\User\VO\Password;
use Tests\Integration\DomainMock\User\VO\UserName;
use Tests\Integration\TestCase;

/**
 * @covers \Rela589n\DoctrineEventSourcing\Event\AggregateChanged
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer\Factory
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedSerializer\Factory\Impl
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer\Factory
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Composed\ComposedDeserializer\Factory\Impl
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToDatabaseValue\Impl
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Util\Converter\ConvertToPHPValue\Impl
 * @covers \Rela589n\DoctrineEventSourcing\Serializer\Util\Database\GetDatabasePlatform\Impl
 * @covers \Rela589n\DoctrineEventSourcing\Event\Concern\TypesMeta\CollectEventSerializeMetaImpl
 * @covers \Rela589n\DoctrineEventSourcing\Event\Concern\TypesMeta\CollectEventSerializeMetaInMemoryCacheDecorator
 */
final class AggregateChangedTest extends TestCase
{
    private ObjectRepository $userRepo;
    private ObjectRepository $userEventsRepo;
    private ObjectRepository $chatRepo;
    private ObjectRepository $chatEventsRepo;
    private ObjectRepository $msgsRepo;
    private ObjectRepository $msgsEventsRepo;

    private array $users;
    private array $chats;

    #[ArrayShape([
        'uuid' => Uuid::class,
        'user' => User::class,
        'chat' => Chat::class,
        'content' => Message::class,
    ])]
    private array $messages = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = $this->entityManager->getRepository(User::class);
        $this->userEventsRepo = $this->entityManager->getRepository(UserEvent::class);
        $this->chatRepo = $this->entityManager->getRepository(Chat::class);
        $this->chatEventsRepo = $this->entityManager->getRepository(ChatEvent::class);
        $this->msgsRepo = $this->entityManager->getRepository(Message::class);
        $this->msgsEventsRepo = $this->entityManager->getRepository(MessageEvent::class);
    }

    protected function tearDown(): void
    {
        $this->removeMany($this->msgsEventsRepo->findAll());
        $this->removeMany($this->msgsRepo->findAll());
        $this->removeMany($this->chatEventsRepo->findAll());
        $this->removeMany($this->chatRepo->findAll());
        $this->removeMany($this->userEventsRepo->findAll());
        $this->removeMany($this->userRepo->findAll());
        $this->entityManager->flush();

        parent::tearDown();
    }

    /**
     * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed\SerializeTyped::from
     * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Typed\DeserializeTyped::from
     */
    public function testTypedAndCastableSerialization(): void
    {
        $chat1 = $this->createChat();
        $this->entityManager->persist($chat1);
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var Chat $chat2 */
        $chat2 = $this->chatRepo->find($chat1->getPrimary());
        self::assertNotSame($chat1, $chat2);
        self::assertEquals($chat1->getPrimary(), $chat2->getPrimary());

        $events = $this->chatEventsRepo->findBy(['entity' => $chat2]);
        self::assertCount(1, $events);

        $this->assertChatCreatedEvent($events[0]);
    }

    /**
     * @covers   \Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded\SerializeEmbedded::from
     * @covers   \Rela589n\DoctrineEventSourcing\Serializer\Separate\Embedded\DeserializeEmbedded::from
     * @covers   \Rela589n\DoctrineEventSourcing\Serializer\Util\Types\TypeIsEmbedded\Impl
     */
    public function testEmbeddedSerialization(): void
    {
        $user1 = $this->registerUser();
        $this->entityManager->persist($user1);
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var User $user2 */
        $user2 = $this->userRepo->find($user1->getPrimary());
        self::assertNotSame($user1, $user2);
        self::assertEquals($user1->getPrimary(), $user2->getPrimary());

        $this->changeUserLogin($user2);
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var User $user3 */
        $user3 = $this->userRepo->find($user2->getPrimary());
        self::assertNotSame($user2, $user3);
        self::assertEquals($user2->getPrimary(), $user3->getPrimary());

        $events = $this->userEventsRepo->findBy(['entity' => $user3->getPrimary()]);
        self::assertCount(2, $events);

        $this->assertUserRegisteredEvent($events[0]);
        $this->assertUserChangedLoginEvent($events[1]);
    }

    /**
     * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\SerializeEntity::from
     * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\DeserializeEntity::from
     * @covers \Rela589n\DoctrineEventSourcing\Serializer\Util\Types\ResolvePrimaryType\Impl
     */
    public function testEntitySerialization(): void
    {
        $user = $this->registerUser();
        $this->entityManager->persist($user);

        $chat = $this->createChat();
        $this->entityManager->persist($chat);

        $message1 = $this->writeMessage($user, $chat);
        $this->entityManager->persist($message1);

        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var Message $message2 */
        $message2 = $this->msgsRepo->find($message1->getPrimary());
        self::assertNotSame($message1, $message2);
        self::assertEquals($message1->getPrimary(), $message2->getPrimary());

        $events = $this->msgsEventsRepo->findBy(['entity' => $message2]);
        self::assertCount(1, $events);

        $this->assertMessageWrittenEvent($events[0]);
    }

    /**
     * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\SerializeEntity::from
     * @covers \Rela589n\DoctrineEventSourcing\Serializer\Separate\Entity\DeserializeEntity::from
     */
    public function testDoesntWrapIntoProxyIfEntityIsLoaded(): void
    {
        $user = $this->registerUser();
        $this->entityManager->persist($user);

        $chat = $this->createChat();
        $this->entityManager->persist($chat);

        $message1 = $this->writeMessage($user, $chat);
        $this->entityManager->persist($message1);

        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var Message $message2 */
        $message2 = $this->msgsRepo->find($message1->getPrimary());
        self::assertNotSame($message1, $message2);
        self::assertEquals($message1->getPrimary(), $message2->getPrimary());

        $user = $message2->_test_prop('user');

        // user is eager loaded, no proxy should be present
        self::assertSame(User::class, $user::class);

        $events = $this->msgsEventsRepo->findBy(['entity' => $message2]);
        self::assertCount(1, $events);

        /** @var MessageWritten $messageWritten */
        $messageWritten = $events[0];
        self::assertInstanceOf(MessageWritten::class, $messageWritten);

        self::assertSame($user, $messageWritten->getUser());
    }

    public function testCastableSerialization(): void
    {
        $chat1 = $this->createChat();
        $this->entityManager->persist($chat1);
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var Chat $chat2 */
        $chat2 = $this->chatRepo->find($chat1->getPrimary());
        self::assertNotSame($chat1, $chat2);
        self::assertEquals($chat1->getPrimary(), $chat2->getPrimary());


        $events = $this->chatEventsRepo->findBy(['entity' => $chat2]);
        self::assertCount(1, $events);

        $this->assertChatCreatedEvent($events[0]);
    }

    public function testDefaultsToNoopSerialization(): void
    {
        $user = $this->registerUser();
        $this->entityManager->persist($user);

        $chat = $this->createChat();
        $this->entityManager->persist($chat);

        $message1 = $this->writeMessage($user, $chat);
        $this->entityManager->persist($message1);

        $this->editMessage($message1);

        $this->entityManager->flush();
        $this->entityManager->clear();

        $events = $this->msgsEventsRepo->findBy(['entity' => $message1]);

        /** @var MessageWasEdited $event */
        $event = end($events);

        $this->assertMessageEditedEvent($event);
    }

    private function removeMany(array $objects): void
    {
        array_map(fn($o) => $this->entityManager->remove($o), $objects);
    }

    private function registerUser(): User
    {
        return User::register(
            Uuid::uuid4(),
            Login::fromString('johndoe@example.com'),
            Password::fromRaw('hello world'),
            UserName::fromString('John Doe'),
        );
    }

    private function assertUserRegisteredEvent(UserRegistered $registeredEvent): void
    {
        self::assertEquals(Login::fromString('johndoe@example.com'), $registeredEvent->getLogin());
        self::assertTrue(password_verify('hello world', (string)$registeredEvent->getPassword()));
        self::assertEquals(UserName::fromString('John Doe'), $registeredEvent->getUserName());
    }

    private function changeUserLogin(User $user)
    {
        $user->changeLogin(Login::fromString('second-john@example.com'));
    }

    private function assertUserChangedLoginEvent(UserChangedLogin $changedLoginEvent): void
    {
        self::assertEquals(Login::fromString('johndoe@example.com'), $changedLoginEvent->getOldLogin());
        self::assertEquals(Login::fromString('second-john@example.com'), $changedLoginEvent->getNewLogin());
    }

    private function createChat(): Chat
    {
        return Chat::create(
            Uuid::uuid4(),
            ChatName::fromString('Some name')
        );
    }

    private function assertChatCreatedEvent(ChatCreated $event): void
    {
        self::assertEquals(ChatName::fromString('Some name'), $event->getChatName());

        $seconds = $event->getShowTutorialAt()
            ->diffInSeconds(Carbon::now());

        self::assertLessThanOrEqual(45, $seconds);
        self::assertGreaterThanOrEqual(43, $seconds);
    }

    private function writeMessage(User $user, Chat $chat): Message
    {
        $uuid = Uuid::uuid4();
        $content = MessageContent::fromString('Some message');

        $this->messages[$uuid->toString()] = [
            'uuid' => $uuid,
            'user' => $user,
            'chat' => $chat,
            'content' => $content,
        ];

        return Message::write($uuid, $user, $chat, $content);
    }

    private function assertMessageWrittenEvent(MessageWritten $event)
    {
        self::assertEquals(MessageContent::fromString('Some message'), $event->getContent());

        $userId = $this->messages[$event->getMessage()
            ->getPrimary()
            ->toString()]['user']->getPrimary();

        self::assertInstanceOf(User::class, $event->getUser());
        self::assertEquals(
            $userId,
            $event->getUser()
                ->getPrimary()
        );
    }

    private function editMessage(Message $message): void
    {
        $message->edit(MessageContent::fromString('Edited content'), false);
    }

    private function assertMessageEditedEvent(MessageWasEdited $event): void
    {
        self::assertEquals(MessageContent::fromString('Edited content'), $event->getNewContent());
        self::assertFalse($event->showWasEdited());
    }
}
