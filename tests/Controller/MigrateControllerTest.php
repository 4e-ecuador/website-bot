<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class MigrateControllerTest extends WebTestCase
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

    public function testIndexRequiresAgent(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/migrate/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAgent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/migrate/');
        self::assertResponseIsSuccessful();
    }

    public function testUploadWithInvalidCsrfToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_POST, '/migrate/upload', [
            'token' => 'invalid',
        ]);
        self::assertResponseStatusCodeSame(400);
    }

    public function testUploadWithNoFileAndInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        // With invalid token → 400 Bad Request (checked first)
        $client->request(Request::METHOD_POST, '/migrate/upload', [
            'token' => '',
        ]);
        self::assertResponseStatusCodeSame(400);
    }

    public function testExportsAllStatColumns(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/migrate/csv');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/csv; charset=utf-8');
        self::assertStringContainsString('attachment; filename=agent-stats-testAgent.csv', (string) $client->getResponse()->headers->get('content-disposition'));

        $csv = Reader::fromString((string) $client->getResponse()->getContent());
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader();
        // The table's column names, without the id and the agent
        self::assertSame('datetime', $header[0]);
        self::assertContains('ap', $header);
        self::assertContains('mind_controller', $header);
        self::assertContains('drone_flight_distance', $header);
        self::assertNotContains('id', $header);
        self::assertNotContains('agent', $header);
        self::assertNotContains('agent_id', $header);

        $rows = iterator_to_array($csv->getRecords(), false);
        self::assertCount(1, $rows);
        self::assertSame('123', $rows[0]['ap']);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $rows[0]['datetime']);
    }

    public function testExportsOnlyTheHeaderWithoutStats(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $faction = $this->getUser()->getAgent()?->getFaction();
        $agent = new Agent()->setNickname('noStats')->setFaction($faction);
        $user = new User()->setEmail('nostats@example.com')->setRoles(['ROLE_AGENT'])->setAgent($agent);
        $em->persist($agent);
        $em->persist($user);
        $em->flush();

        try {
            $client->loginUser($user);
            $client->request(Request::METHOD_GET, '/migrate/csv');

            self::assertResponseIsSuccessful();
            $lines = array_filter(explode("\n", (string) $client->getResponse()->getContent()));
            self::assertCount(1, $lines);
            self::assertStringStartsWith('datetime,', $lines[0]);
        } finally {
            $em->remove($user);
            $em->remove($agent);
            $em->flush();
        }
    }
}
