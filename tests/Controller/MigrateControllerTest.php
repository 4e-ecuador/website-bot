<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class MigrateControllerTest extends WebTestCase
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

    public function testIndexRequiresAgent(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/migrate/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAgent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/migrate/');
        self::assertResponseIsSuccessful();
    }

    public function testUploadWithInvalidCsrfToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_POST, '/migrate/upload', [
            'token' => 'invalid',
        ]);
        self::assertResponseStatusCodeSame(400);
    }

    public function testUploadWithNoFileAndInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        // With invalid token → 400 Bad Request (checked first)
        $client->request(Request::METHOD_POST, '/migrate/upload', [
            'token' => '',
        ]);
        self::assertResponseStatusCodeSame(400);
    }
}
