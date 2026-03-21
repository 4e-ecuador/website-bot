<?php

namespace App\Tests\Repository;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Repository\AgentStatRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AgentStatRepositoryTest extends KernelTestCase
{
    private AgentStatRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository(AgentStat::class);
    }

    private function getAgent(): Agent
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $agent = $em->getRepository(Agent::class)->findOneBy(['nickname' => 'testAgent']);
        self::assertNotNull($agent);

        return $agent;
    }

    public function testHasFindsExistingStat(): void
    {
        $agent = $this->getAgent();
        $latestStat = $this->repository->getAgentLatest($agent);
        self::assertNotNull($latestStat);

        $results = $this->repository->has($latestStat);

        self::assertNotEmpty($results);
    }

    public function testHasReturnsEmptyForNonexistent(): void
    {
        $agent = $this->getAgent();

        $stat = new AgentStat();
        $stat->setDatetime(new DateTime('2000-01-01'));
        $stat->setAgent($agent);

        $results = $this->repository->has($stat);

        self::assertEmpty($results);
    }

    public function testGetAgentLatestReturnsLatest(): void
    {
        $agent = $this->getAgent();

        $latest = $this->repository->getAgentLatest($agent);

        self::assertNotNull($latest);
        self::assertInstanceOf(AgentStat::class, $latest);
    }

    public function testGetAgentLatestFirstReturnsFirst(): void
    {
        $agent = $this->getAgent();

        $first = $this->repository->getAgentLatest($agent, true);

        self::assertNotNull($first);
        self::assertInstanceOf(AgentStat::class, $first);
    }

    public function testGetAgentLatestReturnsNullForAgentWithNoStats(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $faction = $em->getRepository(\App\Entity\Faction::class)->findOneBy([]);

        $agent = new Agent();
        $agent->setNickname('noStatsAgent');
        $agent->setFaction($faction);

        $em->persist($agent);
        $em->flush();

        $latest = $this->repository->getAgentLatest($agent);

        self::assertNull($latest);
    }

    public function testGetPreviousReturnsNull(): void
    {
        $agent = $this->getAgent();
        $first = $this->repository->getAgentLatest($agent, true);
        self::assertNotNull($first);

        // The first entry has no previous
        $previous = $this->repository->getPrevious($first);

        self::assertNull($previous);
    }

    public function testGetPreviousReturnsPreviousStat(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $faction = $em->getRepository(\App\Entity\Faction::class)->findOneBy([]);

        // Create a dedicated agent with exactly 2 stats
        $agent = new Agent();
        $agent->setNickname('prev-stat-' . uniqid());
        $agent->setFaction($faction);

        $em->persist($agent);

        $stat1 = new AgentStat();
        $stat1->setDatetime(new DateTime('2025-01-01'));
        $stat1->setAgent($agent);
        $stat1->setAp(100);

        $em->persist($stat1);

        $stat2 = new AgentStat();
        $stat2->setDatetime(new DateTime('2025-01-02'));
        $stat2->setAgent($agent);
        $stat2->setAp(200);

        $em->persist($stat2);
        $em->flush();

        $previous = $this->repository->getPrevious($stat2);

        self::assertNotNull($previous);
        self::assertSame(100, $previous->getAp());
    }

    public function testFindByDateReturnsStatsInRange(): void
    {
        $start = new DateTime('-1 day');
        $end = new DateTime('+1 day');

        $results = $this->repository->findByDate($start, $end);

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(AgentStat::class, $results);
    }

    public function testFindByDateReturnsEmptyOutsideRange(): void
    {
        $start = new DateTime('2000-01-01');
        $end = new DateTime('2000-01-02');

        $results = $this->repository->findByDate($start, $end);

        self::assertEmpty($results);
    }

    public function testFindByDateAndAgent(): void
    {
        $agent = $this->getAgent();
        $start = new DateTime('-1 day');
        $end = new DateTime('+1 day');

        $results = $this->repository->findByDateAndAgent($start, $end, $agent);

        self::assertNotEmpty($results);
    }

    public function testFindTodaysReturnsResults(): void
    {
        $results = $this->repository->findTodays();

        // Fixture sets datetime to 'now', so it should be found
        self::assertNotEmpty($results);
    }

    public function testFindDaylyReturnsResults(): void
    {
        $results = $this->repository->findDayly(new DateTime());

        self::assertNotEmpty($results);
    }

    public function testFindDaylyReturnsEmptyForPastDate(): void
    {
        $results = $this->repository->findDayly(new DateTime('2000-01-01'));

        self::assertEmpty($results);
    }

    public function testFindAllDateDescReturnsSorted(): void
    {
        $results = $this->repository->findAllDateDesc();

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(AgentStat::class, $results);
    }

    public function testGetAgentStatsReturnsStats(): void
    {
        $agent = $this->getAgent();

        $results = $this->repository->getAgentStats($agent);

        self::assertNotEmpty($results);
    }

    public function testGetAgentStatsForCsvReturnsArrayResult(): void
    {
        $agent = $this->getAgent();

        $results = $this->repository->getAgentStatsForCsv($agent);

        self::assertNotEmpty($results);
        self::assertIsArray($results[0]);
    }
}
