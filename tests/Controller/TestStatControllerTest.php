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

    protected function tearDown(): void
    {
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $em->createQuery("DELETE FROM App\\Entity\\TestStat t WHERE t.csv IN ('test csv data', 'to-edit-csv', 'updated csv data', 'to-delete-csv')")->execute();
        parent::tearDown();
    }

    public function testNewTestStatFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request(Request::METHOD_GET, '/test/stat/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->eq(0)->form();
        $form['test_stat[csv]'] = 'test csv data';
        $client->submit($form);

        self::assertResponseRedirects('/test/stat/');
    }

    public function testEditTestStatFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $toEdit = new TestStat();
        $toEdit->setCsv('to-edit-csv');

        $em->persist($toEdit);
        $em->flush();

        $crawler = $client->request(Request::METHOD_GET, '/test/stat/'.$toEdit->getId().'/edit');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->eq(0)->form();
        $form['test_stat[csv]'] = 'updated csv data';
        $client->submit($form);

        self::assertResponseRedirects('/test/stat/');
    }

    public function testDeleteTestStatWithValidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $toDelete = new TestStat();
        $toDelete->setCsv('to-delete-csv');

        $em->persist($toDelete);
        $em->flush();

        $crawler = $client->request(Request::METHOD_GET, '/test/stat/'.$toDelete->getId());
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request(Request::METHOD_DELETE, '/test/stat/'.$toDelete->getId(), [
            '_token' => $token,
        ]);
        self::assertResponseRedirects('/test/stat/');
    }
}
