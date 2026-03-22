<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Entity\User;
use App\Repository\AgentRepository;
use App\Repository\MapGroupRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests that query parameters are correctly read from the request.
 * These tests verify the fix for the deprecated Request::get() method.
 */
class MapControllerTest extends WebTestCase
{
    public function testMapJsonWithQueryParameter(): void
    {
        $client = static::createClient();

        $user = $this->getAuthenticatedUser();
        if (!$user instanceof User) {
            $this->markTestSkipped('No user found in database');
        }

        $mapGroup = $this->getFirstMapGroup();
        if (!$mapGroup) {
            $this->markTestSkipped('No map group found in database');
        }

        $client->loginUser($user);

        // Test that query parameters are properly read via $request->query->get()
        $client->request(
            Request::METHOD_GET,
            '/map_json',
            ['group' => $mapGroup]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent() ?: '[]');
    }

    public function testMap2JsonWithQueryParameter(): void
    {
        $client = static::createClient();

        $user = $this->getAuthenticatedUser();
        if (!$user instanceof User) {
            $this->markTestSkipped('No user found in database');
        }

        $mapGroup = $this->getFirstMapGroup();
        if (!$mapGroup) {
            $this->markTestSkipped('No map group found in database');
        }

        $client->loginUser($user);

        // Test that query parameters are properly read via $request->query->get()
        $client->request(
            Request::METHOD_GET,
            '/map_json2',
            ['group' => $mapGroup]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent() ?: '[]');
    }

    public function testMapRendersForAgent(): void
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser();
        if (!$user instanceof User) {
            $this->markTestSkipped('No user found in database');
        }

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/map');
        self::assertResponseIsSuccessful();
    }

    public function testMapAgentInfoForEnlAgent(): void
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser();
        if (!$user instanceof User) {
            $this->markTestSkipped('No user found in database');
        }

        $client->loginUser($user);

        $agent = $this->getFirstAgent();
        if (!$agent instanceof Agent) {
            $this->markTestSkipped('No agent found in database');
        }

        $client->request(Request::METHOD_GET, '/map/agent-info/'.$agent->getId());
        self::assertResponseIsSuccessful();
    }

    public function testMap2RendersForAgent(): void
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser();
        if (!$user instanceof User) {
            $this->markTestSkipped('No user found in database');
        }

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/map2');
        self::assertResponseIsSuccessful();
    }

    public function testMap2AgentInfoForEnlAgent(): void
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser();
        if (!$user instanceof User) {
            $this->markTestSkipped('No user found in database');
        }

        $client->loginUser($user);

        $agent = $this->getFirstAgent();
        if (!$agent instanceof Agent) {
            $this->markTestSkipped('No agent found in database');
        }

        $client->request(Request::METHOD_GET, '/map2/agent-info/'.$agent->getId());
        self::assertResponseIsSuccessful();
    }

    private function getAuthenticatedUser(): ?User
    {
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        return $userRepository->findOneBy(['email' => 'admin@example.com']);
    }

    private function getFirstMapGroup(): ?string
    {
        $container = static::getContainer();
        /** @var MapGroupRepository $mapGroupRepository */
        $mapGroupRepository = $container->get(MapGroupRepository::class);
        $mapGroup = $mapGroupRepository->findOneBy([]);

        return $mapGroup?->getName();
    }

    private function getFirstAgent(): ?Agent
    {
        $container = static::getContainer();
        /** @var AgentRepository $agentRepository */
        $agentRepository = $container->get(AgentRepository::class);

        return $agentRepository->findOneBy([]);
    }

    public function testMapJsonWithInvalidGroupThrows(): void
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser();
        if (!$user instanceof User) {
            $this->markTestSkipped('No user found in database');
        }

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/map_json', ['group' => 'nonexistent_group_xyz']);
        self::assertResponseStatusCodeSame(500);
    }

    public function testMapAgentInfoForResAgent(): void
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser();
        if (!$user instanceof User) {
            $this->markTestSkipped('No user found in database');
        }

        $client->loginUser($user);

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $resFaction = $em->getRepository(\App\Entity\Faction::class)->findOneBy(['name' => 'RES']);
        if (!$resFaction instanceof \App\Entity\Faction) {
            $resFaction = new \App\Entity\Faction();
            $resFaction->setName('RES');
            $em->persist($resFaction);
            $em->flush();
        }

        $resAgent = new Agent();
        $resAgent->setNickname('MapResAgent'.uniqid())->setFaction($resFaction);
        $em->persist($resAgent);
        $em->flush();

        try {
            $client->request(Request::METHOD_GET, '/map/agent-info/'.$resAgent->getId());
            self::assertResponseIsSuccessful();
        } finally {
            $em->remove($resAgent);
            $em->flush();
        }
    }

    public function testMapAgentInfoWithRealName(): void
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser();
        if (!$user instanceof User) {
            $this->markTestSkipped('No user found in database');
        }

        $client->loginUser($user);
        $agent = $this->getFirstAgent();
        if (!$agent instanceof Agent) {
            $this->markTestSkipped('No agent found in database');
        }

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $agent->setRealName('Map Real Name Test');
        $em->flush();

        $client->request(Request::METHOD_GET, '/map/agent-info/'.$agent->getId());
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Map Real Name Test', (string) $client->getResponse()->getContent());
    }
}
