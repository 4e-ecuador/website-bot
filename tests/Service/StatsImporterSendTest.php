<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Repository\AgentStatRepository;
use App\Service\CsvParser;
use App\Service\MedalChecker;
use App\Service\StatsImporter;
use App\Service\TelegramAdminMessageHelper;
use App\Service\TelegramMessageHelper;
use App\Type\ImportResult;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TelegramBot\Api\Types\Message;
use UnexpectedValueException;

class StatsImporterSendTest extends TestCase
{
    private function buildImporter(
        TelegramAdminMessageHelper $adminHelper,
        TelegramMessageHelper $messageHelper
    ): StatsImporter {
        $csvParser = $this->createStub(CsvParser::class);
        $medalChecker = $this->createStub(MedalChecker::class);
        $agentStatRepository = $this->createStub(AgentStatRepository::class);
        $translator = $this->createStub(TranslatorInterface::class);

        return new StatsImporter(
            $csvParser,
            $adminHelper,
            $messageHelper,
            $medalChecker,
            $agentStatRepository,
            $translator
        );
    }

    public function testSendResultMessagesThrowsWhenNoAgent(): void
    {
        $adminHelper = $this->createStub(TelegramAdminMessageHelper::class);
        $messageHelper = $this->createStub(TelegramMessageHelper::class);

        $importer = $this->buildImporter($adminHelper, $messageHelper);

        $user = new User();
        // No agent set on user
        $result = new ImportResult();
        $statEntry = new AgentStat();

        $this->expectException(UnexpectedValueException::class);

        $importer->sendResultMessages($result, $statEntry, $user);
    }

    public function testSendResultMessagesWithEnlightenedFactionNoSmurfAlert(): void
    {
        $adminHelper = $this->createMock(TelegramAdminMessageHelper::class);
        $adminHelper->expects(self::never())->method('sendSmurfAlertMessage');

        $messageHelper = $this->createStub(TelegramMessageHelper::class);

        $importer = $this->buildImporter($adminHelper, $messageHelper);

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $statEntry = new AgentStat();
        $statEntry->setFaction('Enlightened');
        $statEntry->setNickname('TestAgent');

        $result = new ImportResult();

        $importer->sendResultMessages($result, $statEntry, $user);
    }

    public function testSendResultMessagesWithNonEnlightenedSendsSmurfAlert(): void
    {
        $adminHelper = $this->createMock(TelegramAdminMessageHelper::class);
        $adminHelper->expects(self::once())->method('sendSmurfAlertMessage')
            ->willReturn(new Message());

        $messageHelper = $this->createStub(TelegramMessageHelper::class);

        $importer = $this->buildImporter($adminHelper, $messageHelper);

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $statEntry = new AgentStat();
        $statEntry->setFaction('Resistance');
        $statEntry->setNickname('TestAgent');

        $result = new ImportResult();

        $importer->sendResultMessages($result, $statEntry, $user);
    }

    public function testSendResultMessagesWithNicknameMismatchSendsMismatchMessage(): void
    {
        $adminHelper = $this->createMock(TelegramAdminMessageHelper::class);
        $adminHelper->expects(self::once())->method('sendNicknameMismatchMessage')
            ->willReturn(new Message());

        $messageHelper = $this->createStub(TelegramMessageHelper::class);

        $importer = $this->buildImporter($adminHelper, $messageHelper);

        $agent = new Agent();
        $agent->setNickname('AgentNick');

        $user = new User();
        $user->setAgent($agent);

        $statEntry = new AgentStat();
        $statEntry->setFaction('Enlightened');
        $statEntry->setNickname('DifferentNick');

        $result = new ImportResult();

        $importer->sendResultMessages($result, $statEntry, $user);
    }

    public function testSendResultMessagesWithMedalUpsSendsMedalMessage(): void
    {
        $adminHelper = $this->createStub(TelegramAdminMessageHelper::class);

        $messageHelper = $this->createMock(TelegramMessageHelper::class);
        $messageHelper->expects(self::once())->method('sendNewMedalMessage')
            ->willReturn(new Message());

        $importer = $this->buildImporter($adminHelper, $messageHelper);

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $statEntry = new AgentStat();
        $statEntry->setFaction('Enlightened');
        $statEntry->setNickname('TestAgent');

        $result = new ImportResult();
        $result->medalUps = ['Guardian' => 3];

        $importer->sendResultMessages($result, $statEntry, $user);
    }

    public function testSendResultMessagesWithMedalDoublesSendsDoubleMessage(): void
    {
        $adminHelper = $this->createStub(TelegramAdminMessageHelper::class);

        $messageHelper = $this->createMock(TelegramMessageHelper::class);
        $messageHelper->expects(self::once())->method('sendMedalDoubleMessage')
            ->willReturn(new Message());

        $importer = $this->buildImporter($adminHelper, $messageHelper);

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $statEntry = new AgentStat();
        $statEntry->setFaction('Enlightened');
        $statEntry->setNickname('TestAgent');

        $result = new ImportResult();
        $result->medalDoubles = ['Hacker' => 5];

        $importer->sendResultMessages($result, $statEntry, $user);
    }

    public function testSendResultMessagesWithNewLevelSendsLevelUpMessage(): void
    {
        $adminHelper = $this->createStub(TelegramAdminMessageHelper::class);

        $messageHelper = $this->createMock(TelegramMessageHelper::class);
        $messageHelper->expects(self::once())->method('sendLevelUpMessage')
            ->willReturn(new Message());

        $importer = $this->buildImporter($adminHelper, $messageHelper);

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $statEntry = new AgentStat();
        $statEntry->setFaction('Enlightened');
        $statEntry->setNickname('TestAgent');

        $result = new ImportResult();
        $result->newLevel = 8;

        $importer->sendResultMessages($result, $statEntry, $user);
    }

    public function testSendResultMessagesWithRecursionsSendsRecursionMessage(): void
    {
        $adminHelper = $this->createStub(TelegramAdminMessageHelper::class);

        $messageHelper = $this->createMock(TelegramMessageHelper::class);
        $messageHelper->expects(self::once())->method('sendRecursionMessage')
            ->willReturn(new Message());

        $importer = $this->buildImporter($adminHelper, $messageHelper);

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $statEntry = new AgentStat();
        $statEntry->setFaction('Enlightened');
        $statEntry->setNickname('TestAgent');

        $result = new ImportResult();
        $result->recursions = 2;

        $importer->sendResultMessages($result, $statEntry, $user);
    }

    public function testGetImportResultWithCoreSubscriptionFirstTime(): void
    {
        $csvParser = $this->createStub(CsvParser::class);

        $medalChecker = $this->createStub(MedalChecker::class);
        $medalChecker->method('getUpgrades')->willReturn([]);
        $medalChecker->method('getDoubles')->willReturn([]);

        $agentStatRepository = $this->createStub(AgentStatRepository::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $adminHelper = $this->createStub(TelegramAdminMessageHelper::class);
        $messageHelper = $this->createStub(TelegramMessageHelper::class);

        $importer = new StatsImporter(
            $csvParser,
            $adminHelper,
            $messageHelper,
            $medalChecker,
            $agentStatRepository,
            $translator
        );

        $previous = new AgentStat();
        $previous->setMonthsSubscribed(null);

        $current = new AgentStat();
        $current->setMonthsSubscribed(1);
        $current->setLevel(5);

        $result = $importer->getImportResult($current, $previous);

        self::assertContains('core', $result->coreSubscribed ?? []);
    }

    public function testGetImportResultWithDualCoreSubscription(): void
    {
        $csvParser = $this->createStub(CsvParser::class);

        $medalChecker = $this->createStub(MedalChecker::class);
        $medalChecker->method('getUpgrades')->willReturn([]);
        $medalChecker->method('getDoubles')->willReturn([]);

        $agentStatRepository = $this->createStub(AgentStatRepository::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $adminHelper = $this->createStub(TelegramAdminMessageHelper::class);
        $messageHelper = $this->createStub(TelegramMessageHelper::class);

        $importer = new StatsImporter(
            $csvParser,
            $adminHelper,
            $messageHelper,
            $medalChecker,
            $agentStatRepository,
            $translator
        );

        $previous = new AgentStat();
        $previous->setMonthsSubscribed(23);

        $current = new AgentStat();
        $current->setMonthsSubscribed(24);
        $current->setLevel(5);

        $result = $importer->getImportResult($current, $previous);

        self::assertContains('dual_core', $result->coreSubscribed ?? []);
    }

    public function testGetImportResultWithCoreYear3Subscription(): void
    {
        $csvParser = $this->createStub(CsvParser::class);

        $medalChecker = $this->createStub(MedalChecker::class);
        $medalChecker->method('getUpgrades')->willReturn([]);
        $medalChecker->method('getDoubles')->willReturn([]);

        $agentStatRepository = $this->createStub(AgentStatRepository::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $adminHelper = $this->createStub(TelegramAdminMessageHelper::class);
        $messageHelper = $this->createStub(TelegramMessageHelper::class);

        $importer = new StatsImporter(
            $csvParser,
            $adminHelper,
            $messageHelper,
            $medalChecker,
            $agentStatRepository,
            $translator
        );

        $previous = new AgentStat();
        $previous->setMonthsSubscribed(35);

        $current = new AgentStat();
        $current->setMonthsSubscribed(36);
        $current->setLevel(5);

        $result = $importer->getImportResult($current, $previous);

        self::assertContains('core_year3', $result->coreSubscribed ?? []);
        self::assertContains('dual_core', $result->coreSubscribed ?? []);
    }

    public function testSendResultMessagesWithIntroRoleUsesIntroGroup(): void
    {
        $adminHelper = $this->createStub(TelegramAdminMessageHelper::class);

        $capturedGroupName = null;
        $messageHelper = $this->createMock(TelegramMessageHelper::class);
        $messageHelper->expects(self::once())->method('sendNewMedalMessage')
            ->willReturnCallback(function (string $groupName) use (&$capturedGroupName): Message {
                $capturedGroupName = $groupName;

                return new Message();
            });

        $importer = $this->buildImporter($adminHelper, $messageHelper);

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);
        $user->setRoles(['ROLE_INTRO_AGENT']);

        $statEntry = new AgentStat();
        $statEntry->setFaction('Enlightened');
        $statEntry->setNickname('TestAgent');

        $result = new ImportResult();
        $result->medalUps = ['Guardian' => 3];

        $importer->sendResultMessages($result, $statEntry, $user);

        self::assertSame('intro', $capturedGroupName);
    }
}
