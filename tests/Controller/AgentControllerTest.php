<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class AgentControllerTest extends WebTestCase
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

    private function getAgent(): Agent
    {
        /** @var Agent $agent */
        $agent = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Agent::class)
            ->findOneBy([]);

        return $agent;
    }

    public function testIndexRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/agent/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAgent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/agent/');
        self::assertResponseIsSuccessful();
    }

    public function testNewRequiresEditor(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/agent/new');
        self::assertResponseIsSuccessful();
    }

    public function testShowAgent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();
        $client->request(Request::METHOD_GET, '/agent/'.$agent->getId());
        self::assertResponseIsSuccessful();
    }

    public function testEditAgentGet(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();
        $client->request(Request::METHOD_GET, '/agent/'.$agent->getId().'/edit');
        self::assertResponseIsSuccessful();
    }

    public function testDeleteRedirectsBack(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();
        $client->request(Request::METHOD_DELETE, '/agent/'.$agent->getId(), [
            '_token' => 'invalid',
        ]);
        self::assertResponseRedirects('/agent/');
    }

    public function testAgentsListReturnsJson(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/agent/list');
        self::assertResponseIsSuccessful();
        self::assertJson((string) $client->getResponse()->getContent());
    }

    public function testAgentsListJsonReturnsJson(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/agent/jsonlist');
        self::assertResponseIsSuccessful();
        self::assertJson((string) $client->getResponse()->getContent());
    }

    public function testAgentsListWithQuery(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/agent/list', ['q' => 'test', 'page' => '1']);
        self::assertResponseIsSuccessful();
    }

    public function testAgentsJsonListWithNickname(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/agent/jsonlist', ['nickname' => 'test']);
        self::assertResponseIsSuccessful();
    }

    public function testLookupReturnsJson(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_POST, '/agent/lookup', ['query' => 'test']);
        self::assertResponseIsSuccessful();
        self::assertJson((string) $client->getResponse()->getContent());
    }

    public function testAddCommentWithInvalidCsrf(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();
        $client->request(Request::METHOD_POST, '/agent/'.$agent->getId().'/add_comment', [
            '_token' => 'invalid',
        ]);
        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('error', $data['error']);
    }

    public function testShowAgentWithLocation(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $agent = $this->getAgent();
        $agent->setLat('-0.180653')->setLon('-78.467834');
        $em->flush();

        $client->request(Request::METHOD_GET, '/agent/'.$agent->getId());
        self::assertResponseIsSuccessful();
    }

    public function testAddCommentWithValidCsrfAndNoComment(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();

        // Get CSRF token from show page
        $crawler = $client->request(Request::METHOD_GET, '/agent/'.$agent->getId());
        $token = $crawler->filter('input[name="_token"]')->last()->attr('value');

        $client->request(Request::METHOD_POST, '/agent/'.$agent->getId().'/add_comment', [
            '_token'    => $token,
            'commenter' => $this->getUser()->getId(),
            'comment'   => '',
        ]);
        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('no comment...', $data['error']);
    }

    public function testAddCommentWithValidCsrfAndInvalidCommenter(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();

        // Get CSRF token from show page
        $crawler = $client->request(Request::METHOD_GET, '/agent/'.$agent->getId());
        $token = $crawler->filter('input[name="_token"]')->last()->attr('value');

        $client->request(Request::METHOD_POST, '/agent/'.$agent->getId().'/add_comment', [
            '_token'    => $token,
            'commenter' => 999999,
            'comment'   => 'Test comment',
        ]);
        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('invalid commenter', $data['error']);
    }

    public function testAddCommentWithValidData(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();
        $user = $this->getUser();

        // Get CSRF token from show page
        $crawler = $client->request(Request::METHOD_GET, '/agent/'.$agent->getId());
        $token = $crawler->filter('input[name="_token"]')->last()->attr('value');

        $client->request(Request::METHOD_POST, '/agent/'.$agent->getId().'/add_comment', [
            '_token'    => $token,
            'commenter' => $user->getId(),
            'comment'   => 'Test comment from test',
        ]);
        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertArrayHasKey('id', $data);
    }
}
