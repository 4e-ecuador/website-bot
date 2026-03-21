<?php

namespace App\Tests\Type;

use App\Entity\Agent;
use App\Entity\User;
use App\Repository\IngressEventRepository;
use App\Service\EmojiService;
use App\Service\MedalChecker;
use App\Type\AbstractCustomMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractCustomMessageTest extends TestCase
{
    /**
     * Create a concrete anonymous subclass of AbstractCustomMessage for testing.
     *
     * @param array<int, string> $messageLines
     */
    private function buildMessage(array $messageLines = ['line one', 'line two']): AbstractCustomMessage
    {
        // EmojiService is final and has no constructor dependencies — use real instance
        $emojiService = new EmojiService();
        $translator = $this->createStub(TranslatorInterface::class);
        $medalChecker = $this->createStub(MedalChecker::class);
        $ingressEventRepository = $this->createStub(IngressEventRepository::class);

        return new class(
            $emojiService,
            $translator,
            $medalChecker,
            $ingressEventRepository,
            'admin@example.com',
            'https://example.com',
            $messageLines,
        ) extends AbstractCustomMessage {
            /** @param array<int, string> $lines */
            public function __construct(
                EmojiService $emojiService,
                TranslatorInterface $translator,
                MedalChecker $medalChecker,
                IngressEventRepository $ingressEventRepository,
                string $announceAdminCc,
                string $pageBaseUrl,
                private readonly array $lines,
            ) {
                parent::__construct(
                    $emojiService,
                    $translator,
                    $medalChecker,
                    $ingressEventRepository,
                    $announceAdminCc,
                    $pageBaseUrl,
                );
            }

            public function getMessage(): array
            {
                return $this->lines;
            }
        };
    }

    public function testGetTextJoinsMessageWithNewlines(): void
    {
        $message = $this->buildMessage(['Hello', 'World']);

        self::assertSame("Hello\nWorld", $message->getText());
    }

    public function testGetTextWithSingleLine(): void
    {
        $message = $this->buildMessage(['Only one line']);

        self::assertSame('Only one line', $message->getText());
    }

    public function testGetTextWithEmptyMessage(): void
    {
        $message = $this->buildMessage([]);

        self::assertSame('', $message->getText());
    }

    public function testGetAgentTelegramNameUsesNicknameWhenNoTelegramName(): void
    {
        $agent = new Agent();
        $agent->setNickname('test_agent');
        // telegram_name defaults to empty string '', which is falsy

        $message = $this->buildMessage();

        $method = new \ReflectionMethod($message, 'getAgentTelegramName');
        $result = $method->invoke($message, $agent);

        self::assertSame('test\\_agent', $result);
    }

    public function testGetAgentTelegramNameUsesTelegramNameWhenSet(): void
    {
        $agent = new Agent();
        $agent->setNickname('nickname_here');
        $agent->setTelegramName('tg_handle_name');

        $message = $this->buildMessage();

        $method = new \ReflectionMethod($message, 'getAgentTelegramName');
        $result = $method->invoke($message, $agent);

        self::assertSame('tg\\_handle\\_name', $result);
    }

    public function testGetAgentTelegramNameEscapesMultipleUnderscores(): void
    {
        $agent = new Agent();
        $agent->setNickname('my_cool_agent');

        $message = $this->buildMessage();

        $method = new \ReflectionMethod($message, 'getAgentTelegramName');
        $result = $method->invoke($message, $agent);

        self::assertSame('my\\_cool\\_agent', $result);
    }

    public function testGetAgentTelegramNameWithNoUnderscores(): void
    {
        $agent = new Agent();
        $agent->setNickname('SimpleAgent');

        $message = $this->buildMessage();

        $method = new \ReflectionMethod($message, 'getAgentTelegramName');
        $result = $method->invoke($message, $agent);

        self::assertSame('SimpleAgent', $result);
    }

    public function testGetAgentUserDataReturnsArray(): void
    {
        $agent = new Agent();
        $agent->setNickname('test_agent');

        $user = new User();
        $user->setEmail('user@example.com');

        $message = $this->buildMessage();

        $method = new \ReflectionMethod($message, 'getAgentUserData');
        $result = $method->invoke($message, $agent, $user);

        self::assertIsArray($result);
    }

    public function testGetAgentUserDataContainsAgentNickname(): void
    {
        $agent = new Agent();
        $agent->setNickname('test_agent');

        $user = new User();
        $user->setEmail('user@example.com');

        $message = $this->buildMessage();

        $method = new \ReflectionMethod($message, 'getAgentUserData');
        $result = $method->invoke($message, $agent, $user);

        self::assertStringContainsString('test_agent', $result[0]);
    }

    public function testGetAgentUserDataContainsUserEmail(): void
    {
        $agent = new Agent();
        $agent->setNickname('SomeAgent');

        $user = new User();
        $user->setEmail('myuser@example.com');

        $message = $this->buildMessage();

        $method = new \ReflectionMethod($message, 'getAgentUserData');
        $result = $method->invoke($message, $agent, $user);

        // getUserAgentName() returns email when no agent is set on user
        $found = false;
        foreach ($result as $line) {
            if (str_contains((string) $line, 'myuser@example.com')) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'User email not found in getAgentUserData output');
    }

    public function testGetAgentUserDataContainsVerifyLine(): void
    {
        $agent = new Agent();
        $agent->setNickname('SomeAgent');

        $user = new User();
        $user->setEmail('user@example.com');

        $message = $this->buildMessage();

        $method = new \ReflectionMethod($message, 'getAgentUserData');
        $result = $method->invoke($message, $agent, $user);

        self::assertContains('Please verify!', $result);
    }

    public function testGetAgentUserDataHasExpectedStructure(): void
    {
        $agent = new Agent();
        $agent->setNickname('AgentSmith');

        $user = new User();
        $user->setEmail('smith@example.com');

        $message = $this->buildMessage();

        $method = new \ReflectionMethod($message, 'getAgentUserData');
        $result = $method->invoke($message, $agent, $user);

        // The array should have exactly 7 elements as per the implementation:
        // Agent line, ID line, empty, User line, ID line, empty, verify line
        self::assertCount(7, $result);
        self::assertStringStartsWith('Agent: ', $result[0]);
        self::assertStringStartsWith('ID: ', $result[1]);
        self::assertSame('', $result[2]);
        self::assertStringStartsWith('User: ', $result[3]);
        self::assertStringStartsWith('ID: ', $result[4]);
        self::assertSame('', $result[5]);
        self::assertSame('Please verify!', $result[6]);
    }
}
