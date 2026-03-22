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

    public function testNewMapGroupFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request(Request::METHOD_GET, '/map/group/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->eq(0)->form();
        $form['map_group[name]'] = 'TestGroupSubmission';
        $client->submit($form);

        self::assertResponseRedirects('/map/group/');

        // Clean up created map group
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $created = $em->getRepository(MapGroup::class)->findOneBy(['name' => 'TestGroupSubmission']);
        if ($created) {
            $em->remove($created);
            $em->flush();
        }
    }

    public function testEditMapGroupFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $mapGroup = $this->getMapGroup();
        $originalName = $mapGroup->getName();
        $crawler = $client->request(Request::METHOD_GET, '/map/group/'.$mapGroup->getId().'/edit');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->eq(1)->form();
        $form['map_group[name]'] = $originalName;
        $client->submit($form);

        self::assertResponseRedirects('/map/group/');
    }

    public function testDeleteMapGroupWithValidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $toDelete = new MapGroup();
        $toDelete->setName('ToDeleteMapGroup');

        $em->persist($toDelete);
        $em->flush();

        $crawler = $client->request(Request::METHOD_GET, '/map/group/'.$toDelete->getId().'/edit');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request(Request::METHOD_DELETE, '/map/group/'.$toDelete->getId(), [
            '_token' => $token,
        ]);
        self::assertResponseRedirects('/map/group/');
    }
}
