<?php

namespace App\Tests\Controller;

use App\Entity\User;
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
        $client->request(Request::METHOD_GET, '/map_json', ['group' => $mapGroup]);

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
        $client->request(Request::METHOD_GET, '/map_json2', ['group' => $mapGroup]);

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent() ?: '[]');
    }

    private function getAuthenticatedUser(): ?User
    {
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        return $userRepository->findOneBy([]);
    }

    private function getFirstMapGroup(): ?string
    {
        $container = static::getContainer();
        /** @var MapGroupRepository $mapGroupRepository */
        $mapGroupRepository = $container->get(MapGroupRepository::class);
        $mapGroup = $mapGroupRepository->findOneBy([]);

        return $mapGroup?->getName();
    }
}
