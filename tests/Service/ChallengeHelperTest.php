<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Service\ChallengeHelper;
use PHPUnit\Framework\TestCase;

class ChallengeHelperTest extends TestCase
{
    private ChallengeHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new ChallengeHelper();
    }

    public function testGetResultsSortedDescending(): void
    {
        $agent1 = new Agent();
        $agent1->setNickname('AgentA');

        $agent2 = new Agent();
        $agent2->setNickname('AgentB');

        $agent3 = new Agent();
        $agent3->setNickname('AgentC');

        $stat1 = new AgentStat();
        $stat1->setAgent($agent1);
        $stat1->setCurrentChallenge(100);

        $stat2 = new AgentStat();
        $stat2->setAgent($agent2);
        $stat2->setCurrentChallenge(300);

        $stat3 = new AgentStat();
        $stat3->setAgent($agent3);
        $stat3->setCurrentChallenge(200);

        $results = $this->helper->getResults([$stat1, $stat2, $stat3]);

        self::assertSame(['AgentB' => 300, 'AgentC' => 200, 'AgentA' => 100], $results);
    }

    public function testGetResultsSkipsNullChallenge(): void
    {
        $agent1 = new Agent();
        $agent1->setNickname('AgentA');

        $agent2 = new Agent();
        $agent2->setNickname('AgentB');

        $stat1 = new AgentStat();
        $stat1->setAgent($agent1);
        $stat1->setCurrentChallenge(100);

        $stat2 = new AgentStat();
        $stat2->setAgent($agent2);
        // No current challenge set (null)

        $results = $this->helper->getResults([$stat1, $stat2]);

        self::assertCount(1, $results);
        self::assertArrayHasKey('AgentA', $results);
    }

    public function testGetResultsSkipsZeroChallenge(): void
    {
        $agent = new Agent();
        $agent->setNickname('AgentA');

        $stat = new AgentStat();
        $stat->setAgent($agent);
        $stat->setCurrentChallenge(0);

        $results = $this->helper->getResults([$stat]);

        self::assertEmpty($results);
    }

    public function testGetResultsEmpty(): void
    {
        $results = $this->helper->getResults([]);

        self::assertEmpty($results);
    }
}
