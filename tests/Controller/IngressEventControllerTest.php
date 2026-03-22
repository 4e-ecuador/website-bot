<?php

namespace App\Tests\Controller;

use App\Entity\IngressEvent;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class IngressEventControllerTest extends WebTestCase
{
    protected function tearDown(): void
    {
        // Restore fixture IngressEvent with ID=1 if it was deleted, for ControllerAccessTest
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        if (!$em->getRepository(IngressEvent::class)->find(1)) {
            $conn = $em->getConnection();
            $now = new \DateTime()->format('Y-m-d H:i:s');
            $conn->executeStatement(
                'INSERT INTO ingress_event (id, name, type, date_start, date_end, description) VALUES (1, :name, :type, :ds, :de, :desc)',
                ['name' => 'test', 'type' => 'fs', 'ds' => $now, 'de' => $now, 'desc' => 'test']
            );
        }

        parent::tearDown();
    }

    private function getUser(): User
    {
        /** @var User $user */
        $user = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        return $user;
    }

    private function getIngressEvent(): IngressEvent
    {
        /** @var IngressEvent $event */
        $event = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(IngressEvent::class)
            ->findOneBy([]);

        return $event;
    }

    public function testIndexRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/ingress/event/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/ingress/event/');
        self::assertResponseIsSuccessful();
    }

    public function testNewEventRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/ingress/event/new');
        self::assertResponseIsSuccessful();
    }

    public function testShowEventForAdminRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $event = $this->getIngressEvent();
        $client->request(Request::METHOD_GET, '/ingress/event/'.$event->getId());
        self::assertResponseIsSuccessful();
    }

    public function testPublicShowRendersPage(): void
    {
        $client = static::createClient();
        $event = $this->getIngressEvent();
        $client->request(Request::METHOD_GET, '/ingress/event/show/'.$event->getId());
        self::assertResponseIsSuccessful();
    }

    public function testEditEventRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $event = $this->getIngressEvent();
        $client->request(Request::METHOD_GET, '/ingress/event/'.$event->getId().'/edit');
        self::assertResponseIsSuccessful();
    }

    public function testDeleteEventWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $event = $this->getIngressEvent();
        $client->request(Request::METHOD_DELETE, '/ingress/event/'.$event->getId(), [
            '_token' => 'invalid',
        ]);
        self::assertResponseRedirects('/ingress/event/');
    }

    public function testAnnounceRedirectsWithNoEvents(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/ingress/event/announce');
        // Redirects to index (no future FS events in fixtures)
        self::assertResponseRedirects('/ingress/event/');
    }

    public function testAnnounceFbmRedirects(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/ingress/event/announce-fbm');
        self::assertResponseRedirects('/ingress/event/');
    }

    public function testAnnounceFbmTokenRedirects(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/ingress/event/announce-fbm-token');
        self::assertResponseRedirects('/ingress/event/');
    }

    public function testNewEventFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request(Request::METHOD_GET, '/ingress/event/new');
        self::assertResponseIsSuccessful();

        // Only one form on the new page
        $form = $crawler->filter('form')->eq(0)->form();
        $form['ingress_event[name]'] = 'Test Event Name';
        $form['ingress_event[type]'] = 'fs';
        $form['ingress_event[link]'] = 'https://example.com';
        $form['ingress_event[date_start]'] = '2024-06-15T10:00';
        $form['ingress_event[date_end]'] = '2024-06-15T18:00';
        $form['ingress_event[description]'] = 'Test description';
        $client->submit($form);

        self::assertResponseRedirects('/ingress/event/');
    }

    public function testEditEventFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $event = $this->getIngressEvent();
        $crawler = $client->request(Request::METHOD_GET, '/ingress/event/'.$event->getId().'/edit');
        self::assertResponseIsSuccessful();

        // Edit page has delete form first, then the main form (index 1)
        $form = $crawler->filter('form')->eq(1)->form();
        $form['ingress_event[name]'] = 'Updated Event Name';
        $form['ingress_event[type]'] = 'md';
        $form['ingress_event[link]'] = 'https://example.com/updated';
        $form['ingress_event[date_start]'] = '2024-07-01T09:00';
        $form['ingress_event[date_end]'] = '2024-07-01T17:00';
        $form['ingress_event[description]'] = 'Updated description';
        $client->submit($form);

        self::assertResponseRedirects('/ingress/event/');
    }

    public function testDeleteEventWithValidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        // Create a dedicated event for deletion to avoid deleting fixture data
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $toDelete = new IngressEvent();
        $toDelete->setName('ToDeleteEvent')
            ->setType('fs')
            ->setDateStart(new \DateTime())
            ->setDateEnd(new \DateTime());
        $em->persist($toDelete);
        $em->flush();

        // Get the CSRF token from the show page delete form
        $crawler = $client->request(Request::METHOD_GET, '/ingress/event/'.$toDelete->getId());
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request(Request::METHOD_DELETE, '/ingress/event/'.$toDelete->getId(), [
            '_token' => $token,
        ]);
        self::assertResponseRedirects('/ingress/event/');
    }
}
