<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class TestControllerTest extends WebTestCase
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

    public function testIndexRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/test/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/test/');
        self::assertResponseIsSuccessful();
    }

    public function testBotTestRendersWithoutText(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/test/bot');
        self::assertResponseIsSuccessful();
    }

    public function testMailTestRendersWithoutText(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/test/mail');
        self::assertResponseIsSuccessful();
    }

    public function testTestEmojisRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/test/emojis✨');
        self::assertResponseIsSuccessful();
    }

    public function testModifyStatsRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/test/modify-stats');
        self::assertResponseIsSuccessful();
    }

    public function testModifyStatsInputWithNoCsv(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/test/modify-stats/input');
        self::assertResponseStatusCodeSame(406);
    }

    public function testModifyStatsInputWithValidCsv(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $csv = "key1\tkey2\nval1\tval2";
        $client->request(Request::METHOD_GET, '/test/modify-stats/input', ['q' => $csv]);
        self::assertResponseIsSuccessful();
    }

    public function testModifyStatsInputWithInvalidCsv(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/test/modify-stats/input', ['q' => 'no tabs here']);
        self::assertResponseStatusCodeSame(406);
    }
}
