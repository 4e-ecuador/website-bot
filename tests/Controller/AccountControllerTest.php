<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class AccountControllerTest extends WebTestCase
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

    public function testAccountRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/account');
        self::assertResponseRedirects();
    }

    public function testAccountRendersForUser(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/account');
        self::assertResponseIsSuccessful();
    }

    public function testTelegramDisconnectRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/account/tg-disconnect');
        self::assertResponseRedirects();
    }

    public function testTelegramDisconnectRedirects(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/account/tg-disconnect');
        self::assertResponseRedirects('/account');
    }

    public function testTelegramConnectRedirects(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/account/tg-connect');
        // Should redirect to telegram connect link
        self::assertResponseRedirects();
    }

    public function testAccountFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request(Request::METHOD_GET, '/account');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->first()->form();
        $form['agent_account[real_name]'] = 'Test User';
        $client->submit($form);

        self::assertResponseRedirects();
    }
}
