<?php

namespace App\Tests\Controller;

use App\Entity\AgentStat;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class AgentStatControllerTest extends WebTestCase
{
    private function getUser(): User
    {
        /** @var User $user */
        $user = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        return $user;
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

    public function testIndexRequiresAgent(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/agent-stat/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAgent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/agent-stat/');
        self::assertResponseIsSuccessful();
    }

    public function testNewStatRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/agent-stat/new');
        self::assertResponseIsSuccessful();
    }

    public function testShowStatRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $stat = $this->getAgentStat();
        $client->request(Request::METHOD_GET, '/agent-stat/'.$stat->getId());
        self::assertResponseIsSuccessful();
    }

    public function testEditStatRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $stat = $this->getAgentStat();
        $client->request(Request::METHOD_GET, '/agent-stat/'.$stat->getId().'/edit');
        self::assertResponseIsSuccessful();
    }

    public function testDeleteStatWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $stat = $this->getAgentStat();
        $client->request(Request::METHOD_DELETE, '/agent-stat/'.$stat->getId(), [
            '_token' => 'invalid',
        ]);
        self::assertResponseRedirects('/agent-stat/');
    }
}
