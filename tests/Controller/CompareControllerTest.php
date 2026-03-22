<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class CompareControllerTest extends WebTestCase
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

    private function getAgent(): Agent
    {
        /** @var Agent $agent */
        $agent = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Agent::class)
            ->findOneBy([]);

        return $agent;
    }

    public function testIndexRequiresAgent(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/compare');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAgent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/compare');
        self::assertResponseIsSuccessful();
    }

    public function testIndexWithSearchTerm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/compare', ['q' => 'test']);
        self::assertResponseIsSuccessful();
    }

    public function testPreviewRendersAgents(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/compare-preview', [
            'q'        => 'test',
            'excludes' => '[]',
        ]);
        self::assertResponseIsSuccessful();
    }

    public function testAgentListRendersWithIds(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();
        $client->request(Request::METHOD_GET, '/compare-agent-list', [
            'agents' => json_encode([(string) $agent->getId()]),
        ]);
        self::assertResponseIsSuccessful();
    }

    public function testCompareResultWithIds(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();
        $client->request(Request::METHOD_GET, '/compare-result', [
            'agents' => json_encode([(string) $agent->getId()]),
        ]);
        self::assertResponseIsSuccessful();
    }

    public function testCompareResultWithEmptyIds(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/compare-result', [
            'agents' => '[]',
        ]);
        self::assertResponseIsSuccessful();
    }

    public function testAgentListWithEmptyIds(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/compare-agent-list', [
            'agents' => '[]',
        ]);
        self::assertResponseIsSuccessful();
    }
}
