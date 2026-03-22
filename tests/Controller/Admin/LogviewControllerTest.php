<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class LogviewControllerTest extends WebTestCase
{
    private function getAdminUser(): User
    {
        /** @var User $user */
        $user = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        return $user;
    }

    public function testLogviewRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/admin/logview');
        self::assertResponseRedirects();
    }

    public function testLogviewRendersForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());
        $client->request(Request::METHOD_GET, '/admin/logview');
        self::assertResponseIsSuccessful();
    }

    public function testLogviewWithDeployLogFile(): void
    {
        $projectDir = dirname(__DIR__, 3);
        $logFile = $projectDir.'/var/log/deploy.log';
        $logContent = ">>>==============\n2024-01-01 12:00:00\nSome log entry\n<<<==============\n";

        file_put_contents($logFile, $logContent);

        try {
            $client = static::createClient();
            $client->loginUser($this->getAdminUser());
            $client->request(Request::METHOD_GET, '/admin/logview');
            self::assertResponseIsSuccessful();
        } finally {
            if (file_exists($logFile)) {
                unlink($logFile);
            }
        }
    }
}
