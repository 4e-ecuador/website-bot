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

    protected function tearDown(): void
    {
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $em->createQuery("DELETE FROM App\\Entity\\Challenge c WHERE c.name IN ('FormSubmissionChallenge', 'EditSubmissionChallenge', 'ToDeleteChallenge')")->execute();
        parent::tearDown();
    }

    public function testNewChallengeFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request(Request::METHOD_GET, '/challenge/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->eq(0)->form();
        $form['challenge[name]'] = 'FormSubmissionChallenge';
        $form['challenge[codeName]'] = 'form-submission-challenge';
        $form['challenge[date_start]'] = '2099-06-15T10:00';
        $form['challenge[date_end]'] = '2099-06-15T18:00';
        $client->submit($form);

        self::assertResponseRedirects('/challenge/');
    }

    public function testEditChallengeFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $toEdit = new Challenge();
        $toEdit->setName('EditSubmissionChallenge')
            ->setCodeName('edit-submission')
            ->setDateStart(new \DateTime('2099-01-01'))
            ->setDateEnd(new \DateTime('2099-01-01'));
        $em->persist($toEdit);
        $em->flush();

        $crawler = $client->request(Request::METHOD_GET, '/challenge/'.$toEdit->getId().'/edit');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->eq(0)->form();
        $form['challenge[name]'] = 'EditSubmissionChallenge';
        $form['challenge[codeName]'] = 'edit-submission-updated';
        $form['challenge[date_start]'] = '2099-07-01T09:00';
        $form['challenge[date_end]'] = '2099-07-01T17:00';
        $client->submit($form);

        self::assertResponseRedirects('/challenge/');
    }

    public function testDeleteChallengeWithValidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $toDelete = new Challenge();
        $toDelete->setName('ToDeleteChallenge')
            ->setCodeName('to-delete')
            ->setDateStart(new \DateTime('2099-01-01'))
            ->setDateEnd(new \DateTime('2099-01-01'));
        $em->persist($toDelete);
        $em->flush();

        $crawler = $client->request(Request::METHOD_GET, '/challenge/'.$toDelete->getId().'/edit');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request(Request::METHOD_DELETE, '/challenge/'.$toDelete->getId(), [
            '_token' => $token,
        ]);
        self::assertResponseRedirects('/challenge/');
    }
}
