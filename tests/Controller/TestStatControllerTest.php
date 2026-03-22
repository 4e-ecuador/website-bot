<?php

namespace App\Tests\Controller;

use App\Entity\TestStat;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class TestStatControllerTest extends WebTestCase
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

    private function getTestStat(): TestStat
    {
        /** @var TestStat $testStat */
        $testStat = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(TestStat::class)
            ->findOneBy([]);

        return $testStat;
    }

    public function testIndexRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/test/stat/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/test/stat/');
        self::assertResponseIsSuccessful();
    }

    public function testNewTestStatRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/test/stat/new');
        self::assertResponseIsSuccessful();
    }

    public function testShowTestStatRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $testStat = $this->getTestStat();
        $client->request(Request::METHOD_GET, '/test/stat/'.$testStat->getId());
        self::assertResponseIsSuccessful();
    }

    public function testEditTestStatRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $testStat = $this->getTestStat();
        $client->request(Request::METHOD_GET, '/test/stat/'.$testStat->getId().'/edit');
        self::assertResponseIsSuccessful();
    }

    public function testDeleteTestStatWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $testStat = $this->getTestStat();
        $client->request(Request::METHOD_DELETE, '/test/stat/'.$testStat->getId(), [
            '_token' => 'invalid',
        ]);
        self::assertResponseRedirects('/test/stat/');
    }
}
