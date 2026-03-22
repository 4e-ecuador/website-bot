<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class AboutControllerTest extends WebTestCase
{
    private function getAdminUser(): User
    {
        /** @var User $user */
        $user = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        return $user;
    }

    public function testAboutRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/admin/about');
        self::assertResponseRedirects();
    }

    public function testAboutRendersForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());
        $client->request(Request::METHOD_GET, '/admin/about');
        self::assertResponseIsSuccessful();
    }
}
