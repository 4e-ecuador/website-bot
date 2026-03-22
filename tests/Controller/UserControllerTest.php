<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends WebTestCase
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

    public function testIndexRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/user/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/user/');
        self::assertResponseIsSuccessful();
    }

    public function testAgentsListRequiresAgent(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/user/list');
        self::assertResponseRedirects();
    }

    public function testAgentsListReturnsJson(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/user/list');
        self::assertResponseIsSuccessful();
        self::assertJson((string) $client->getResponse()->getContent());
    }

    public function testAgentsListWithQuery(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/user/list', ['q' => 'admin', 'page' => '1']);
        self::assertResponseIsSuccessful();
    }

    public function testIndexJsonRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/user/jsonlist');
        self::assertResponseRedirects();
    }

    public function testIndexJsonReturnsJson(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/user/jsonlist');
        self::assertResponseIsSuccessful();
        self::assertJson((string) $client->getResponse()->getContent());
    }

    public function testNewUserRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/user/new');
        self::assertResponseIsSuccessful();
    }

    public function testShowUserRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $user = $this->getUser();
        $client->request(Request::METHOD_GET, '/user/'.$user->getId());
        self::assertResponseIsSuccessful();
    }

    public function testEditUserRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $user = $this->getUser();
        $client->request(Request::METHOD_GET, '/user/'.$user->getId().'/edit');
        self::assertResponseIsSuccessful();
    }

    public function testDeleteUserWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $user = $this->getUser();
        $client->request(Request::METHOD_DELETE, '/user/'.$user->getId(), [
            '_token' => 'invalid',
        ]);
        self::assertResponseRedirects('/user/');
    }

    public function testEditUserFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        // Create a temp user to edit (avoid corrupting the fixture admin user)
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $tempUser = $em->getRepository(User::class)->findOneBy(['email' => 'temp-edit-test@example.com']);
        if (!$tempUser instanceof User) {
            $tempUser = new User();
            $tempUser->setEmail('temp-edit-test@example.com')->setRoles(['ROLE_USER']);
            $em->persist($tempUser);
            $em->flush();
        }

        $crawler = $client->request(Request::METHOD_GET, '/user/'.$tempUser->getId().'/edit');
        self::assertResponseIsSuccessful();

        // The edit page has delete form first, edit form last
        $form = $crawler->filter('form')->last()->form();
        $client->submit($form);

        // Redirects to user index (with optional id param)
        self::assertResponseRedirects();
    }

    public function testIndexJsonWithQuery(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/user/jsonlist', ['q' => 'test', 'page' => '1']);
        self::assertResponseIsSuccessful();
        self::assertJson((string) $client->getResponse()->getContent());
    }
}
