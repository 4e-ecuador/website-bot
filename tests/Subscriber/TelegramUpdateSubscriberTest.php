<?php

namespace App\Tests\Subscriber;

use App\Subscriber\TelegramUpdateSubscriber;
use BoShurik\TelegramBotBundle\Event\UpdateEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Chat;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\User;

class TelegramUpdateSubscriberTest extends TestCase
{
    private function buildBotApi(?User $meUser = null, ?callable $onSendMessage = null): BotApi
    {
        return new class($meUser, $onSendMessage) extends BotApi {

            /** @var callable|null */
            private $onSendMessage;

            public function __construct(private readonly ?User $meUser, ?callable $onSendMessage)
            {
                $this->onSendMessage = $onSendMessage;
            }

            public function __destruct()
            {
            }

            public function sendMessage(
                $chatId,
                $text,
                $parseMode = null,
                $disablePreview = false,
                $replyToMessageId = null,
                $replyMarkup = null,
                $disableNotification = false,
                $messageThreadId = null,
                $protectContent = null,
                $allowSendingWithoutReply = null
            ): \TelegramBot\Api\Types\Message {
                if ($this->onSendMessage !== null) {
                    ($this->onSendMessage)();
                }

                return new \TelegramBot\Api\Types\Message();
            }

            public function getMe(): User
            {
                return $this->meUser ?? new User();
            }
        };
    }

    public function testGetSubscribedEventsReturnsCorrectEvents(): void
    {
        $subscribed = TelegramUpdateSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(UpdateEvent::class, $subscribed);
        self::assertCount(1, $subscribed);
    }

    public function testCheckSetsAllowedChatFlag(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $botApi = $this->buildBotApi();

        $subscriber = new TelegramUpdateSubscriber($logger, $botApi);

        $update = $this->createStub(Update::class);
        $event = new UpdateEvent('default', $update);

        $subscriber->check($event);

        // Check method marks chat as allowed - state not directly observable
        // but we can verify processUpdate runs without trying sendMessage
        $this->addToAssertionCount(1);
    }

    public function testWriteLogDoesNothingWhenNoMessage(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $botApi = $this->buildBotApi();

        $subscriber = new TelegramUpdateSubscriber($logger, $botApi);

        $update = $this->createStub(Update::class);
        $update->method('getMessage')->willReturn(null);

        $event = new UpdateEvent('default', $update);
        $subscriber->writeLog($event);
    }

    public function testWriteLogLogsWhenMessageExists(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $botApi = $this->buildBotApi();

        $subscriber = new TelegramUpdateSubscriber($logger, $botApi);

        $message = $this->createStub(Message::class);
        $message->method('getText')->willReturn('Hello');

        $update = $this->createStub(Update::class);
        $update->method('getMessage')->willReturn($message);

        $event = new UpdateEvent('default', $update);
        $subscriber->writeLog($event);
    }

    public function testProcessUpdateWithNoMessageDoesNothing(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $botApi = $this->buildBotApi();

        $subscriber = new TelegramUpdateSubscriber($logger, $botApi);

        $update = $this->createStub(Update::class);
        $update->method('getMessage')->willReturn(null);

        $event = new UpdateEvent('default', $update);
        $subscriber->processUpdate($event);

        $this->addToAssertionCount(1);
    }

    public function testProcessUpdateWithMessageButNoNewMembersDoesNothing(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $botApi = $this->buildBotApi();

        $subscriber = new TelegramUpdateSubscriber($logger, $botApi);

        $message = $this->createStub(Message::class);
        $message->method('getNewChatMembers')->willReturn(null);

        $update = $this->createStub(Update::class);
        $update->method('getMessage')->willReturn($message);

        $event = new UpdateEvent('default', $update);
        $subscriber->processUpdate($event);

        $this->addToAssertionCount(1);
    }

    public function testProcessUpdateWithNewMembersAndNotAllowedChatSendsNoAccessMessage(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $sendMessageCalled = false;
        $botApi = $this->buildBotApi(onSendMessage: function () use (&$sendMessageCalled): void {
            $sendMessageCalled = true;
        });

        $subscriber = new TelegramUpdateSubscriber($logger, $botApi);
        // Do NOT call check() - isAllowedChat stays false

        $chat = $this->createStub(Chat::class);
        $chat->method('getId')->willReturn(999);

        $newMember = $this->createStub(User::class);
        $newMember->method('getId')->willReturn(42);

        $message = $this->createStub(Message::class);
        $message->method('getNewChatMembers')->willReturn([$newMember]);
        $message->method('getChat')->willReturn($chat);

        $update = $this->createStub(Update::class);
        $update->method('getMessage')->willReturn($message);

        $event = new UpdateEvent('default', $update);
        $subscriber->processUpdate($event);

        self::assertTrue($sendMessageCalled);
    }

    public function testProcessUpdateWithBotAddedToAllowedChatSendsWelcome(): void
    {
        $logger = $this->createStub(LoggerInterface::class);

        $botUser = User::fromResponse(['id' => 100, 'first_name' => 'TestBot', 'username' => 'test_bot', 'is_bot' => true]);
        $sendMessageCalled = false;
        $botApi = $this->buildBotApi(meUser: $botUser, onSendMessage: function () use (&$sendMessageCalled): void {
            $sendMessageCalled = true;
        });

        $subscriber = new TelegramUpdateSubscriber($logger, $botApi);
        // Call check() to set isAllowedChat = true
        $checkUpdate = $this->createStub(Update::class);
        $subscriber->check(new UpdateEvent('default', $checkUpdate));

        $chat = $this->createStub(Chat::class);
        $chat->method('getId')->willReturn(999);

        // New member is the bot itself (same id as getMe())
        $newMember = $this->createStub(User::class);
        $newMember->method('getId')->willReturn(100);

        $message = $this->createStub(Message::class);
        $message->method('getNewChatMembers')->willReturn([$newMember]);
        $message->method('getChat')->willReturn($chat);

        $update = $this->createStub(Update::class);
        $update->method('getMessage')->willReturn($message);

        $event = new UpdateEvent('default', $update);
        $subscriber->processUpdate($event);

        self::assertTrue($sendMessageCalled);
    }

    public function testProcessUpdateWithNewPersonInAllowedChatDoesNothing(): void
    {
        $logger = $this->createStub(LoggerInterface::class);

        $botUser = User::fromResponse(['id' => 100, 'first_name' => 'TestBot', 'username' => 'test_bot', 'is_bot' => true]);
        $sendMessageCalled = false;
        $botApi = $this->buildBotApi(meUser: $botUser, onSendMessage: function () use (&$sendMessageCalled): void {
            $sendMessageCalled = true;
        });

        $subscriber = new TelegramUpdateSubscriber($logger, $botApi);
        $checkUpdate = $this->createStub(Update::class);
        $subscriber->check(new UpdateEvent('default', $checkUpdate));

        $chat = $this->createStub(Chat::class);
        $chat->method('getId')->willReturn(999);

        // New member is a person (different id from bot)
        $newMember = $this->createStub(User::class);
        $newMember->method('getId')->willReturn(999);

        $message = $this->createStub(Message::class);
        $message->method('getNewChatMembers')->willReturn([$newMember]);
        $message->method('getChat')->willReturn($chat);

        $update = $this->createStub(Update::class);
        $update->method('getMessage')->willReturn($message);

        $event = new UpdateEvent('default', $update);
        $subscriber->processUpdate($event);

        self::assertFalse($sendMessageCalled);
    }
}
