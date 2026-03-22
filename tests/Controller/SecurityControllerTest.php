<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginRendersForAnonymous(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/login');
        self::assertResponseIsSuccessful();
    }

    public function testLoginRedirectsForAuthenticatedUser(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/login');
        self::assertResponseRedirects();
    }
}
