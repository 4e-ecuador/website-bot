<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class MailControllerTest extends WebTestCase
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

    public function testSendConfirmationMailRequiresAdmin(): void
    {
        $client = static::createClient();
        $user = $this->getAdminUser();
        $client->request(Request::METHOD_GET, '/mailer/send-confirmation-mail/'.$user->getId());
        self::assertResponseRedirects();
    }

    public function testSendConfirmationMailSendsEmail(): void
    {
        $client = static::createClient();
        $user = $this->getAdminUser();
        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/mailer/send-confirmation-mail/'.$user->getId());
        self::assertResponseIsSuccessful();
    }
}
