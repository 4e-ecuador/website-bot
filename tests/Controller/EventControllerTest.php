<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class EventControllerTest extends WebTestCase
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

    private function getEvent(): Event
    {
        /** @var Event $event */
        $event = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Event::class)
            ->findOneBy([]);

        return $event;
    }

    public function testIndexRequiresAgent(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/event/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAgent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/event/');
        self::assertResponseIsSuccessful();
    }

    public function testNewEventRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/event/new');
        self::assertResponseIsSuccessful();
    }

    public function testShowEventRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $event = $this->getEvent();
        $client->request(Request::METHOD_GET, '/event/'.$event->getId());
        self::assertResponseIsSuccessful();
    }

    public function testEditEventRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $event = $this->getEvent();
        $client->request(Request::METHOD_GET, '/event/'.$event->getId().'/edit');
        self::assertResponseIsSuccessful();
    }

    public function testDeleteEventWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $event = $this->getEvent();
        $client->request(Request::METHOD_DELETE, '/event/'.$event->getId(), [
            '_token' => 'invalid',
        ]);
        self::assertResponseRedirects('/event/');
    }

    protected function tearDown(): void
    {
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $em->createQuery("DELETE FROM App\\Entity\\Event e WHERE e.name IN ('FormSubmissionEvent', 'EditSubmissionEvent', 'ToDeleteEvent')")->execute();
        parent::tearDown();
    }

    public function testNewEventFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request(Request::METHOD_GET, '/event/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->eq(0)->form();
        $form['event[name]'] = 'FormSubmissionEvent';
        $form['event[eventType]'] = 'ap';
        $form['event[date_start]'] = '2099-06-15T10:00';
        $form['event[date_end]'] = '2099-06-15T18:00';
        $client->submit($form);

        self::assertResponseRedirects('/event/');
    }

    public function testEditEventFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $toEdit = new Event();
        $toEdit->setName('EditSubmissionEvent')
            ->setDateStart(new \DateTime('2099-01-01'))
            ->setDateEnd(new \DateTime('2099-01-01'));
        $em->persist($toEdit);
        $em->flush();

        $crawler = $client->request(Request::METHOD_GET, '/event/'.$toEdit->getId().'/edit');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->eq(0)->form();
        $form['event[name]'] = 'EditSubmissionEvent';
        $form['event[eventType]'] = 'hacker';
        $form['event[date_start]'] = '2099-07-01T09:00';
        $form['event[date_end]'] = '2099-07-01T17:00';
        $client->submit($form);

        self::assertResponseRedirects('/event/');
    }

    public function testDeleteEventWithValidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $toDelete = new Event();
        $toDelete->setName('ToDeleteEvent')
            ->setDateStart(new \DateTime('2099-01-01'))
            ->setDateEnd(new \DateTime('2099-01-01'));
        $em->persist($toDelete);
        $em->flush();

        $crawler = $client->request(Request::METHOD_GET, '/event/'.$toDelete->getId().'/edit');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request(Request::METHOD_DELETE, '/event/'.$toDelete->getId(), [
            '_token' => $token,
        ]);
        self::assertResponseRedirects('/event/');
    }
}
