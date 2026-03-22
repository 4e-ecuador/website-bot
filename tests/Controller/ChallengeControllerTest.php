<?php

namespace App\Tests\Controller;

use App\Entity\Challenge;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ChallengeControllerTest extends WebTestCase
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

    private function getChallenge(): Challenge
    {
        /** @var Challenge $challenge */
        $challenge = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Challenge::class)
            ->findOneBy([]);

        return $challenge;
    }

    public function testIndexRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/challenge/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/challenge/');
        self::assertResponseIsSuccessful();
    }

    public function testNewChallengeRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/challenge/new');
        self::assertResponseIsSuccessful();
    }

    public function testShowChallengeRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $challenge = $this->getChallenge();
        $client->request(Request::METHOD_GET, '/challenge/'.$challenge->getId());
        self::assertResponseIsSuccessful();
    }

    public function testEditChallengeRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $challenge = $this->getChallenge();
        $client->request(Request::METHOD_GET, '/challenge/'.$challenge->getId().'/edit');
        self::assertResponseIsSuccessful();
    }

    public function testDeleteChallengeWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $challenge = $this->getChallenge();
        $client->request(Request::METHOD_DELETE, '/challenge/'.$challenge->getId(), [
            '_token' => 'invalid',
        ]);
        self::assertResponseRedirects('/challenge/');
    }
}
