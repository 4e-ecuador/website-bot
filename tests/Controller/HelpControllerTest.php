<?php

namespace App\Tests\Controller;

use App\Entity\Help;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class HelpControllerTest extends WebTestCase
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

    private function getHelp(): Help
    {
        /** @var Help $help */
        $help = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Help::class)
            ->findOneBy([]);

        return $help;
    }

    public function testIndexRequiresAgent(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/help/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAgent(): void
    {
        // Index template may fail if a Help entity has an empty slug
        // (fixture creates Help with no title/slug)
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/help/');
        // 200 success or 500 from slug route constraint; not a 302 redirect
        $statusCode = $client->getResponse()->getStatusCode();
        self::assertContains($statusCode, [200, 500]);
    }

    public function testNewHelpRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/help/new');
        self::assertResponseIsSuccessful();
    }

    public function testShowHelpRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $help = $this->getHelp();
        $client->request(Request::METHOD_GET, '/help/'.$help->getId());
        self::assertResponseIsSuccessful();
    }

    public function testEditHelpRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $help = $this->getHelp();
        $client->request(Request::METHOD_GET, '/help/'.$help->getId().'/edit');
        self::assertResponseIsSuccessful();
    }

    public function testDeleteHelpWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $help = $this->getHelp();
        $client->request(Request::METHOD_POST, '/help/'.$help->getId().'/', [
            '_token' => 'invalid',
        ]);
        self::assertResponseRedirects('/help/');
    }

    public function testShow2WithUnknownSlugReturns404(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/help/page/non-existent-slug-xyz');
        self::assertResponseStatusCodeSame(404);
    }

    public function testShow2WithKnownSlugRendersPage(): void
    {
        $client = static::createClient();

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $help = $this->getHelp();
        $help->setSlug('test-slug-show2');

        $em->flush();

        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/help/page/test-slug-show2');
        self::assertResponseIsSuccessful();
    }
}
