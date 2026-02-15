<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Repository\AgentStatRepository;
use App\Service\LeaderBoardService;
use PHPUnit\Framework\TestCase;

class LeaderBoardServiceTest extends TestCase
{
    private LeaderBoardService $service;

    private AgentStatRepository&\PHPUnit\Framework\MockObject\Stub $statRepository;

    protected function setUp(): void
    {
        $this->statRepository = $this->createStub(AgentStatRepository::class);
        $this->service = new LeaderBoardService($this->statRepository);
    }

    public function testGetBoardSkipsUsersWithoutAgent(): void
    {
        $user = new User();

        $result = $this->service->getBoard([$user]);

        self::assertSame([], $result);
    }

    public function testGetBoardSkipsUsersWithoutStats(): void
    {
        $agent = new Agent();
        $user = new User();
        $user->setAgent($agent);

        $this->statRepository->method('getAgentLatest')->willReturn(null);

        $result = $this->service->getBoard([$user]);

        self::assertSame([], $result);
    }

    public function testGetBoardWithZeroConnectorSkipsFieldsLinks(): void
    {
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $stat = new AgentStat();
        $stat->setAp(1000);
        $stat->setConnector(0);
        $stat->setMindController(100);

        $this->statRepository->method('getAgentLatest')->willReturn($stat);

        $result = $this->service->getBoard([$user]);

        self::assertArrayNotHasKey('Fields/Links', $result);
    }

    public function testGetBoardWithNullConnectorSkipsFieldsLinks(): void
    {
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $stat = new AgentStat();
        $stat->setAp(1000);
        $stat->setConnector(null);
        $stat->setMindController(100);

        $this->statRepository->method('getAgentLatest')->willReturn($stat);

        $result = $this->service->getBoard([$user]);

        self::assertArrayNotHasKey('Fields/Links', $result);
    }

    public function testGetBoardWithValidConnectorIncludesFieldsLinks(): void
    {
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $stat = new AgentStat();
        $stat->setAp(1000);
        $stat->setConnector(10);
        $stat->setMindController(50);

        $this->statRepository->method('getAgentLatest')->willReturn($stat);

        $result = $this->service->getBoard([$user]);

        self::assertArrayHasKey('Fields/Links', $result);
        self::assertSame(5.0, $result['Fields/Links'][0]->getValue()); // @phpstan-ignore offsetAccess.nonOffsetAccessible
    }

    public function testGetBoardWithTypeOnlyFilter(): void
    {
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $stat = new AgentStat();
        $stat->setAp(1000);
        $stat->setConnector(10);
        $stat->setMindController(50);

        $this->statRepository->method('getAgentLatest')->willReturn($stat);

        $result = $this->service->getBoard([$user], 'ap');

        self::assertNotEmpty($result);
        self::assertEquals(1000, $result[0]->getValue()); // @phpstan-ignore method.nonObject
    }

    public function testGetBoardWithUnknownTypeThrows(): void
    {
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $user = new User();
        $user->setAgent($agent);

        $stat = new AgentStat();
        $stat->setAp(1000);

        $this->statRepository->method('getAgentLatest')->willReturn($stat);

        $this->expectException(\UnexpectedValueException::class);

        $this->service->getBoard([$user], 'nonexistent_type');
    }
}
