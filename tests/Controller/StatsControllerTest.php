<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StatsControllerTest extends WebTestCase
{
    public function testByDateWithQueryParameters(): void
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }

        $client->loginUser($user);

        $client->request('GET', '/stats/by-date', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        // Should properly read query parameters without throwing errors
        $this->assertResponseIsSuccessful();
    }

    public function testByDateWithoutParameters(): void
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }

        $client->loginUser($user);

        $client->request('GET', '/stats/by-date');

        // Should handle missing parameters gracefully
        $this->assertResponseIsSuccessful();
    }

    private function getAuthenticatedUser(): ?User
    {
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        return $userRepository->findOneBy([]);
    }
}
