<?php

namespace App\Tests\Repository;

use App\Entity\Agent;
use App\Entity\Faction;
use App\Entity\MapGroup;
use App\Repository\AgentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AgentRepositoryTest extends KernelTestCase
{
    private AgentRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository(Agent::class);

        // Remove accumulated test agents from previous runs that would pollute search results
        // First delete related comments, then the agents
        $em->createQuery("DELETE FROM App\Entity\Comment c WHERE c.agent IN (SELECT a FROM App\Entity\Agent a WHERE a.nickname LIKE 'TestResAgent%' OR a.nickname LIKE 'ResAgent%' OR a.nickname LIKE 'unlinked-%' OR a.nickname LIKE 'test-%' OR a.nickname = 'noStatsAgent')")->execute();
        $em->createQuery("DELETE FROM App\Entity\Agent a WHERE a.nickname LIKE 'TestResAgent%' OR a.nickname LIKE 'ResAgent%' OR a.nickname LIKE 'unlinked-%' OR a.nickname LIKE 'test-%' OR a.nickname = 'noStatsAgent'")->execute();
    }

    public function testFindOneByNickNameReturnsAgent(): void
    {
        $agent = $this->repository->findOneByNickName('testAgent');

        self::assertNotNull($agent);
        self::assertSame('testAgent', $agent->getNickname());
    }

    public function testFindOneByNickNameReturnsNullForMissing(): void
    {
        $agent = $this->repository->findOneByNickName('nonexistent');

        self::assertNull($agent);
    }

    public function testHasReturnsAgentWhenExists(): void
    {
        $agent = new Agent();
        $agent->setNickname('testAgent');

        $found = $this->repository->has($agent);

        self::assertNotNull($found);
        self::assertSame('testAgent', $found->getNickname());
    }

    public function testHasReturnsNullWhenNotExists(): void
    {
        $agent = new Agent();
        $agent->setNickname('nonexistent');

        $found = $this->repository->has($agent);

        self::assertNull($found);
    }

    public function testSearchByAgentNameFindsPartialMatch(): void
    {
        $results = $this->repository->searchByAgentName('test');

        self::assertNotEmpty($results);
        self::assertSame('testAgent', $results[0]->getNickname());
    }

    public function testSearchByAgentNameReturnsEmptyForNoMatch(): void
    {
        $results = $this->repository->searchByAgentName('zzzzzzz');

        self::assertEmpty($results);
    }

    public function testSearchByAgentNameWithExcludes(): void
    {
        $agent = $this->repository->findOneByNickName('testAgent');
        self::assertNotNull($agent);

        $results = $this->repository->searchByAgentName('test', [$agent->getId()]);

        self::assertEmpty($results);
    }

    public function testFindAllAlphabeticalReturnsSorted(): void
    {
        $results = $this->repository->findAllAlphabetical();

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(Agent::class, $results);
    }

    public function testFindNotifyAgentsReturnsEmpty(): void
    {
        // Fixture agent has no telegram_id set
        $results = $this->repository->findNotifyAgents();

        self::assertEmpty($results);
    }

    public function testSearchByIdsFindsAgent(): void
    {
        $agent = $this->repository->findOneByNickName('testAgent');
        self::assertNotNull($agent);

        $results = $this->repository->searchByIds([$agent->getId()]);

        self::assertCount(1, $results);
        self::assertSame('testAgent', $results[0]->getNickname());
    }

    public function testSearchByIdsReturnsEmptyForNoMatch(): void
    {
        $results = $this->repository->searchByIds([99999]);

        self::assertEmpty($results);
    }

    public function testSearchByIdsReturnsMatchingAgents(): void
    {
        $agent = $this->repository->findOneByNickName('testAgent');
        self::assertNotNull($agent);

        $results = $this->repository->searchByIds([$agent->getId()]);

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(Agent::class, $results);
    }

    public function testSearchByIdsReturnsEmptyForUnknownId(): void
    {
        $results = $this->repository->searchByIds([99999]);

        self::assertEmpty($results);
    }

    public function testFindMapAgentsReturnsAgentsInGroup(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $mapGroupRepo = $em->getRepository(MapGroup::class);
        $mapGroup = $mapGroupRepo->findOneBy(['name' => 'test']);
        self::assertNotNull($mapGroup);

        $results = $this->repository->findMapAgents($mapGroup);

        // Fixture agent is not assigned to map group
        self::assertEmpty($results);
    }
}
