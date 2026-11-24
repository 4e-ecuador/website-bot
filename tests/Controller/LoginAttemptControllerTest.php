<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class LoginAttemptControllerTest extends WebTestCase
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

    public function testIndexRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/login-attempt/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());
        $client->request(Request::METHOD_GET, '/login-attempt/');
        self::assertResponseIsSuccessful();
    }

    public function testIndexWithEmailFilter(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());
        $client->request(Request::METHOD_GET, '/login-attempt/', [
            'paginatorOptions' => ['criteria' => ['email' => 'test@example.com']],
        ]);
        self::assertResponseIsSuccessful();
    }

    public function testIndexReadsPostedPaginatorOptions(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());
        $client->request(Request::METHOD_POST, '/login-attempt/', [
            'paginatorOptions' => [
                'page' => '3',
                'limit' => '5',
                'order' => 'email',
                'orderDir' => 'DESC',
                'criteria' => ['email' => 'nobody'],
            ],
        ]);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="paginatorOptions[page]"][value="3"]');
        self::assertSelectorExists('input[name="paginatorOptions[order]"][value="email"]');
        self::assertSelectorExists('input[name="paginatorOptions[orderDir]"][value="DESC"]');
        self::assertSelectorTextSame('select[name="paginatorOptions[limit]"] option[selected]', '5');
        self::assertInputValueSame('paginatorOptions[criteria][email]', 'nobody');
    }
}
