<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class StatsControllerTest extends WebTestCase
{
    private function getAuthenticatedUser(): User
    {
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'admin@example.com']);
        if (!$user instanceof User) {
            $this->markTestSkipped('No user found in database');
        }

        return $user;
    }

    private function getAgent(): Agent
    {
        /** @var Agent $agent */
        $agent = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Agent::class)
            ->findOneBy([]);
        return $agent;
    }

    private function getAgentStat(): AgentStat
    {
        /** @var AgentStat $stat */
        $stat = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(AgentStat::class)
            ->findOneBy([]);
        return $stat;
    }

    public function testByDateWithQueryParameters(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAuthenticatedUser());

        $client->request(Request::METHOD_GET, '/stats/by-date', [
            'start_date' => '2024-01-01',
            'end_date'   => '2024-01-31',
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testByDateWithoutParameters(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAuthenticatedUser());

        $client->request(Request::METHOD_GET, '/stats/by-date');

        $this->assertResponseIsSuccessful();
    }

    public function testAgentStatsRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAuthenticatedUser());

        $agent = $this->getAgent();

        $client->request(Request::METHOD_GET, '/stats/agent/'.$agent->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testAgentStatsJsonReturnsData(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAuthenticatedUser());

        $agent = $this->getAgent();

        $client->request(
            Request::METHOD_GET,
            '/stats/agent/data/'.$agent->getId().'/2020-01-01/2030-01-01'
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson((string) $client->getResponse()->getContent());
    }

    public function testLeaderBoardRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAuthenticatedUser());

        $client->request(Request::METHOD_GET, '/stats/leaderboard');

        $this->assertResponseIsSuccessful();
    }

    public function testLeaderBoardDetailReturnsHtml(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAuthenticatedUser());

        $client->request(Request::METHOD_POST, '/stats/leaderboard-detail', [
            'item' => 'ap',
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testInBetweenRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAuthenticatedUser());

        $client->request(Request::METHOD_GET, '/stats/in-between');

        $this->assertResponseIsSuccessful();
    }

    public function testInBetweenResultWithValidDates(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAuthenticatedUser());

        $stat = $this->getAgentStat();
        $datetime = $stat->getDatetime()?->format('Y-n-j H:i:s') ?? '';

        $client->request(Request::METHOD_GET, '/stats/in-between-result', [
            'dateStart' => $datetime,
            'dateEnd'   => $datetime,
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testInBetweenResultWithInvalidDates(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAuthenticatedUser());

        $client->request(Request::METHOD_GET, '/stats/in-between-result', [
            'dateStart' => '1900-01-01',
            'dateEnd'   => '1900-01-02',
        ]);

        $this->assertResponseIsSuccessful();
    }
}
