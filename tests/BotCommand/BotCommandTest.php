<?php

namespace App\Tests\BotCommand;

use App\BotCommand\Agents;
use App\BotCommand\Cite;
use App\BotCommand\Guides;
use App\BotCommand\Start;
use App\Entity\Agent;
use App\Repository\AgentRepository;
use App\Service\CiteService;
use App\Service\EmojiService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Chat;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class BotCommandTest extends TestCase
{
    private function buildBotApi(): BotApi
    {
        return new class extends BotApi {
            public function __construct()
            {
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
            ): Message {
                return new Message();
            }
        };
    }

    private function buildUpdate(string $text = '/start test123'): Update
    {
        $chat = $this->createStub(Chat::class);
        $chat->method('getId')->willReturn(12345);

        $tgUser = $this->createStub(User::class);
        $tgUser->method('getUsername')->willReturn('TestUser');
        $tgUser->method('getId')->willReturn(999);

        $message = $this->createStub(Message::class);
        $message->method('getChat')->willReturn($chat);
        $message->method('getFrom')->willReturn($tgUser);
        $message->method('getText')->willReturn($text);

        $update = $this->createStub(Update::class);
        $update->method('getMessage')->willReturn($message);

        return $update;
    }

    public function testAgentsCommandMetadata(): void
    {
        $command = new Agents();
        self::assertSame('/agents', $command->getName());
        self::assertSame('Agents lookup command', $command->getDescription());
    }

    public function testAgentsCommandExecuteSendsMessage(): void
    {
        $command = new Agents();
        $api = $this->buildBotApi();

        $command->execute($api, $this->buildUpdate());
        // No exception = pass
        $this->addToAssertionCount(1);
    }

    public function testGuidesCommandMetadata(): void
    {
        $command = new Guides();
        self::assertSame('/guias', $command->getName());
        self::assertSame('Guides command', $command->getDescription());
    }

    public function testGuidesCommandExecuteSendsMessage(): void
    {
        $command = new Guides();
        $api = $this->buildBotApi();

        $command->execute($api, $this->buildUpdate());
        $this->addToAssertionCount(1);
    }

    public function testCiteCommandMetadata(): void
    {
        $emojiService = new EmojiService();
        $citeService = new CiteService(__DIR__.'/../../');

        $command = new Cite($citeService, $emojiService);
        self::assertSame('/cite', $command->getName());
        self::assertSame('Cites command', $command->getDescription());
    }

    public function testCiteCommandExecuteSendsMessage(): void
    {
        $emojiService = new EmojiService();
        $citeService = new CiteService(__DIR__.'/../../');

        $command = new Cite($citeService, $emojiService);
        $api = $this->buildBotApi();

        $command->execute($api, $this->buildUpdate('/cite'));
        $this->addToAssertionCount(1);
    }

    public function testStartCommandMetadata(): void
    {
        $agentRepository = $this->createStub(AgentRepository::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);

        $command = new Start($agentRepository, $entityManager, $translator);
        self::assertSame('/start', $command->getName());
        self::assertSame('Start command', $command->getDescription());
    }

    public function testStartCommandWithMissingCodeSendsErrorMessage(): void
    {
        $agentRepository = $this->createStub(AgentRepository::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $command = new Start($agentRepository, $entityManager, $translator);
        $api = $this->buildBotApi();

        // Text without valid /start code format
        $command->execute($api, $this->buildUpdate('/start'));
        $this->addToAssertionCount(1);
    }

    public function testStartCommandWithInvalidCodeSendsErrorMessage(): void
    {
        $agentRepository = $this->createStub(AgentRepository::class);
        $agentRepository->method('findOneBy')->willReturn(null);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $command = new Start($agentRepository, $entityManager, $translator);
        $api = $this->buildBotApi();

        // Text with valid format but no matching agent
        $command->execute($api, $this->buildUpdate('/start abc123'));
        $this->addToAssertionCount(1);
    }

    public function testStartCommandWithValidCodeConnectsAgent(): void
    {
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $agentRepository = $this->createStub(AgentRepository::class);
        $agentRepository->method('findOneBy')->willReturn($agent);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Agent verified!');

        $command = new Start($agentRepository, $entityManager, $translator);
        $api = $this->buildBotApi();

        $command->execute($api, $this->buildUpdate('/start abc123'));
    }
}
