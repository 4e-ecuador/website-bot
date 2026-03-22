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
}
