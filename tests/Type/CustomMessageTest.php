<?php

namespace App\Tests\Type;

use App\Entity\Agent;
use App\Repository\IngressEventRepository;
use App\Service\EmojiService;
use App\Service\MedalChecker;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Type\CustomMessage\LevelUpMessage;
use App\Type\CustomMessage\MedalDoubleMessage;
use App\Type\CustomMessage\NewMedalMessage;
use App\Type\CustomMessage\RecursionMessage;
use App\Type\CustomMessage\SmurfAlertMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomMessageTest extends TestCase
{
    /**
     * @return array{EmojiService, TranslatorInterface, MedalChecker, IngressEventRepository}
     */
    private function buildDependencies(): array
    {
        $emojiService = new EmojiService();
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);
        $medalChecker = $this->createStub(MedalChecker::class);
        $medalChecker->method('translateMedalLevel')->willReturn('Gold');
        $ingressEventRepository = $this->createStub(IngressEventRepository::class);

        return [$emojiService, $translator, $medalChecker, $ingressEventRepository];
    }

    private function buildAgent(): Agent
    {
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        return $agent;
    }

    public function testMedalDoubleMessageGetTextReturnsString(): void
    {
        [$emojiService, $translator, $medalChecker, $ingressEventRepository] = $this->buildDependencies();

        $message = new MedalDoubleMessage(
            $emojiService,
            $translator,
            $medalChecker,
            $ingressEventRepository,
            'admin@example.com',
            'https://example.com'
        );
        $message->setAgent($this->buildAgent());
        $message->setMedalDoubles(['Sojourner' => 3]);

        $text = $message->getText();
        self::assertGreaterThanOrEqual(0, strlen($text));
        self::assertNotEmpty($text);
    }

    public function testMedalDoubleMessageWithEmptyDoubles(): void
    {
        [$emojiService, $translator, $medalChecker, $ingressEventRepository] = $this->buildDependencies();

        $message = new MedalDoubleMessage(
            $emojiService,
            $translator,
            $medalChecker,
            $ingressEventRepository,
            'admin@example.com',
            'https://example.com'
        );
        $message->setAgent($this->buildAgent());
        $message->setMedalDoubles([]);

        $text = $message->getText();
        self::assertGreaterThanOrEqual(0, strlen($text));
    }

    public function testNewMedalMessageGetTextReturnsString(): void
    {
        [$emojiService, $translator, $medalChecker, $ingressEventRepository] = $this->buildDependencies();

        $message = new NewMedalMessage(
            $emojiService,
            $translator,
            $medalChecker,
            $ingressEventRepository,
            'admin@example.com',
            'https://example.com'
        );
        $message->setAgent($this->buildAgent());
        $message->setMedalUps(['Explorer' => 2]);

        $text = $message->getText();
        self::assertGreaterThanOrEqual(0, strlen($text));
        self::assertNotEmpty($text);
    }

    public function testNewMedalMessageWithMultipleMedals(): void
    {
        [$emojiService, $translator, $medalChecker, $ingressEventRepository] = $this->buildDependencies();

        $message = new NewMedalMessage(
            $emojiService,
            $translator,
            $medalChecker,
            $ingressEventRepository,
            'admin@example.com',
            'https://example.com'
        );
        $message->setAgent($this->buildAgent());
        $message->setMedalUps(['Explorer' => 2, 'Sojourner' => 3]);

        $text = $message->getText();
        self::assertGreaterThanOrEqual(0, strlen($text));
    }

    public function testLevelUpMessageGetTextReturnsString(): void
    {
        [$emojiService, $translator, $medalChecker, $ingressEventRepository] = $this->buildDependencies();

        $message = new LevelUpMessage(
            $emojiService,
            $translator,
            $medalChecker,
            $ingressEventRepository,
            'admin@example.com',
            'https://example.com'
        );
        $message->setAgent($this->buildAgent());
        $message->setLevel(16);
        $message->setRecursions(0);

        $text = $message->getText();
        self::assertGreaterThanOrEqual(0, strlen($text));
        self::assertNotEmpty($text);
    }

    public function testLevelUpMessageWithRecursions(): void
    {
        [$emojiService, $translator, $medalChecker, $ingressEventRepository] = $this->buildDependencies();

        $message = new LevelUpMessage(
            $emojiService,
            $translator,
            $medalChecker,
            $ingressEventRepository,
            'admin@example.com',
            'https://example.com'
        );
        $message->setAgent($this->buildAgent());
        $message->setLevel(16);
        $message->setRecursions(2);

        $text = $message->getText();
        self::assertGreaterThanOrEqual(0, strlen($text));
        self::assertStringContainsString('16+', $text);
    }

    public function testRecursionMessageSingleRecursion(): void
    {
        [$emojiService, $translator, $medalChecker, $ingressEventRepository] = $this->buildDependencies();

        $message = new RecursionMessage(
            $emojiService,
            $translator,
            $medalChecker,
            $ingressEventRepository,
            'admin@example.com',
            'https://example.com'
        );
        $message->setAgent($this->buildAgent());
        $message->setRecursions(1);

        $text = $message->getText();
        self::assertGreaterThanOrEqual(0, strlen($text));
        self::assertNotEmpty($text);
    }

    public function testRecursionMessageMultipleRecursions(): void
    {
        [$emojiService, $translator, $medalChecker, $ingressEventRepository] = $this->buildDependencies();

        $message = new RecursionMessage(
            $emojiService,
            $translator,
            $medalChecker,
            $ingressEventRepository,
            'admin@example.com',
            'https://example.com'
        );
        $message->setAgent($this->buildAgent());
        $message->setRecursions(3);

        $text = $message->getText();
        self::assertStringContainsString('X 3', $text);
    }

    public function testSmurfAlertMessageGetTextReturnsString(): void
    {
        [$emojiService, $translator, $medalChecker, $ingressEventRepository] = $this->buildDependencies();

        $message = new SmurfAlertMessage(
            $emojiService,
            $translator,
            $medalChecker,
            $ingressEventRepository,
            'admin@example.com',
            'https://example.com'
        );

        $user = new User();
        $agent = $this->buildAgent();

        $statEntry = new AgentStat();
        $statEntry->setFaction('Resistance');

        $message->setUser($user);
        $message->setAgent($agent);
        $message->setStatEntry($statEntry);
        $message->setAnnounceAdminCc('cc@example.com');

        $text = $message->getText();
        self::assertGreaterThanOrEqual(0, strlen($text));
        self::assertStringContainsString('SMURF ALERT', $text);
        self::assertStringContainsString('Resistance', $text);
    }
}
