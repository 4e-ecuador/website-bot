<?php

namespace App\Tests\Command;

use App\Command\UpdateAgentTgConnectionSecretCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateAgentTgConnectionSecretCommandTest extends KernelTestCase
{
    public function testExecuteUpdatesAgentsWithoutSecret(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('UpdateAgentTgConnectionSecret');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Database has been updated', $commandTester->getDisplay());
    }
}
