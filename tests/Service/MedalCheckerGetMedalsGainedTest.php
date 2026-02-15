<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Repository\AgentStatRepository;
use App\Service\MedalChecker;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class MedalCheckerGetMedalsGainedTest extends TestCase
{
    private MedalChecker $medalChecker;

    private AgentStatRepository&\PHPUnit\Framework\MockObject\Stub $statRepository;

    protected function setUp(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $this->medalChecker = new MedalChecker(
            $translator,
            dirname(__DIR__, 2),
            'test',
        );

        $this->statRepository = $this->createStub(AgentStatRepository::class);
    }

    public function testEmptyEntriesReturnsEmptyResults(): void
    {
        $result = $this->medalChecker->getMedalsGained([], $this->statRepository);

        self::assertSame([], $result['byDate']);
        self::assertSame([], $result['byMedal']);
    }

    public function testDetectsMedalGain(): void
    {
        $agent = new Agent()->setNickname('TestAgent');

        $previousStat = new AgentStat()
            ->setAgent($agent)
            ->setDatetime(new DateTime('2024-01-01'))
            ->setExplorer(50);

        $currentStat = new AgentStat()
            ->setAgent($agent)
            ->setDatetime(new DateTime('2024-01-02'))
            ->setExplorer(150);

        $this->statRepository->method('getPrevious')->willReturn($previousStat);

        $result = $this->medalChecker->getMedalsGained(
            [$previousStat, $currentStat],
            $this->statRepository,
        );

        self::assertArrayHasKey('2024-01-02', $result['byDate']);
        self::assertArrayHasKey('TestAgent', $result['byDate']['2024-01-02']);
        self::assertArrayHasKey('explorer', $result['byDate']['2024-01-02']['TestAgent']);

        self::assertArrayHasKey('explorer', $result['byMedal']);
        self::assertSame('TestAgent', $result['byMedal']['explorer'][0]['agent']);
    }

    public function testNoGainWhenLevelUnchanged(): void
    {
        $agent = new Agent()->setNickname('TestAgent');

        $stat1 = new AgentStat()
            ->setAgent($agent)
            ->setDatetime(new DateTime('2024-01-01'))
            ->setExplorer(150);

        $stat2 = new AgentStat()
            ->setAgent($agent)
            ->setDatetime(new DateTime('2024-01-02'))
            ->setExplorer(200);

        $this->statRepository->method('getPrevious')->willReturn($stat1);

        $result = $this->medalChecker->getMedalsGained(
            [$stat1, $stat2],
            $this->statRepository,
        );

        // Both at bronze level (100-999), no gain
        if (isset($result['byDate']['2024-01-02']['TestAgent']['explorer'])) {
            // The level should be the same — no upgrade detected
            self::fail('Should not detect explorer gain when level is unchanged');
        }

        self::assertArrayNotHasKey('explorer', $result['byMedal']);
    }

    public function testByMedalSortedByLevelDescending(): void
    {
        $agent1 = new Agent()->setNickname('Agent1');

        $agent2 = new Agent()->setNickname('Agent2');

        $stat1a = new AgentStat()
            ->setAgent($agent1)
            ->setDatetime(new DateTime('2024-01-01'))
            ->setExplorer(50);

        $stat1b = new AgentStat()
            ->setAgent($agent1)
            ->setDatetime(new DateTime('2024-01-02'))
            ->setExplorer(1500);

        $stat2a = new AgentStat()
            ->setAgent($agent2)
            ->setDatetime(new DateTime('2024-01-01'))
            ->setExplorer(50);

        $stat2b = new AgentStat()
            ->setAgent($agent2)
            ->setDatetime(new DateTime('2024-01-02'))
            ->setExplorer(150);

        $this->statRepository->method('getPrevious')->willReturn(null);

        $result = $this->medalChecker->getMedalsGained(
            [$stat1a, $stat1b, $stat2a, $stat2b],
            $this->statRepository,
        );

        self::assertArrayHasKey('explorer', $result['byMedal']);

        $explorerEntries = $result['byMedal']['explorer'];
        self::assertGreaterThanOrEqual(2, count($explorerEntries));
        // Higher level should come first
        self::assertGreaterThanOrEqual(
            $explorerEntries[1]['level'],
            $explorerEntries[0]['level'],
        );
    }
}
