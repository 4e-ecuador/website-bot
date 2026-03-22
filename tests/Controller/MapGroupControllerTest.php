<?php

namespace App\Tests\Controller;

use App\Entity\MapGroup;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class MapGroupControllerTest extends WebTestCase
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

    private function getMapGroup(): MapGroup
    {
        /** @var MapGroup $mapGroup */
        $mapGroup = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(MapGroup::class)
            ->findOneBy([]);

        return $mapGroup;
    }

    public function testIndexRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/map/group/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/map/group/');
        self::assertResponseIsSuccessful();
    }

    public function testNewMapGroupRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/map/group/new');
        self::assertResponseIsSuccessful();
    }

    public function testEditMapGroupRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $mapGroup = $this->getMapGroup();
        $client->request(Request::METHOD_GET, '/map/group/'.$mapGroup->getId().'/edit');
        self::assertResponseIsSuccessful();
    }

    public function testDeleteMapGroupWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $mapGroup = $this->getMapGroup();
        $client->request(Request::METHOD_DELETE, '/map/group/'.$mapGroup->getId(), [
            '_token' => 'invalid',
        ]);
        self::assertResponseRedirects('/map/group/');
    }
}
