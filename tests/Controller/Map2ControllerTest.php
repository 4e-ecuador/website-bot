<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class Map2ControllerTest extends WebTestCase
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

    public function testMapRequiresAgent(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/map2');
        self::assertResponseRedirects();
    }

    public function testMapRendersForAgent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/map2');
        self::assertResponseIsSuccessful();
    }

    public function testMapJsonWithValidGroup(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        // The fixture creates a MapGroup named 'test'
        $client->request(Request::METHOD_GET, '/map_json2', ['group' => 'test']);
        self::assertResponseIsSuccessful();
        self::assertJson((string) $client->getResponse()->getContent());
    }

    public function testMapJsonWithInvalidGroupThrows(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/map_json2', ['group' => 'nonexistent_group_xyz']);
        // Should throw UnexpectedValueException → 500
        self::assertResponseStatusCodeSame(500);
    }

    public function testMapAgentInfoForEnlAgent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();
        $client->request(Request::METHOD_GET, '/map2/agent-info/'.$agent->getId());
        self::assertResponseIsSuccessful();
    }

    public function testMapAgentInfoForResAgent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $em = static::getContainer()->get('doctrine.orm.entity_manager');

        // Get or create RES faction
        $resFaction = $em->getRepository(\App\Entity\Faction::class)->findOneBy(['name' => 'RES']);
        if (!$resFaction instanceof \App\Entity\Faction) {
            $resFaction = new \App\Entity\Faction();
            $resFaction->setName('RES');
            $em->persist($resFaction);
            $em->flush();
        }

        // Create a RES agent for this test
        $resAgent = new Agent();
        $resAgent->setNickname('ResAgent'.uniqid())->setFaction($resFaction);
        $em->persist($resAgent);
        $em->flush();

        try {
            $client->request(Request::METHOD_GET, '/map2/agent-info/'.$resAgent->getId());
            self::assertResponseIsSuccessful();
        } finally {
            $em->remove($resAgent);
            $em->flush();
        }
    }

    public function testMapAgentInfoWithRealName(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $agent = $this->getAgent();
        $agent->setRealName('Test Real Name');

        $em->flush();

        $client->request(Request::METHOD_GET, '/map2/agent-info/'.$agent->getId());
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Test Real Name', (string) $client->getResponse()->getContent());
    }
}
