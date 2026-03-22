<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class MarkdownControllerTest extends WebTestCase
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

    public function testPreviewRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_POST, '/markdown/preview');
        self::assertResponseRedirects();
    }

    public function testPreviewWithTextReturnsJson(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_POST, '/markdown/preview', ['text' => '**bold**']);
        self::assertResponseIsSuccessful();
        self::assertJson((string) $client->getResponse()->getContent());
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertArrayHasKey('data', $data);
    }

    public function testPreviewWithEmptyTextReturnsDefault(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_POST, '/markdown/preview', ['text' => '']);
        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame(':(', $data['data']);
    }
}
