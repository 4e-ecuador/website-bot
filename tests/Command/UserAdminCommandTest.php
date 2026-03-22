<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UserAdminCommandTest extends KernelTestCase
{
    public function testExecuteWithExitOption(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('user-admin');
        $commandTester = new CommandTester($command);

        // Provide '5' to select 'Exit'
        $commandTester->setInputs(['5']);
        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('have Fun', $commandTester->getDisplay());
    }

    public function testExecuteWithListOption(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('user-admin');
        $commandTester = new CommandTester($command);

        // Select 'List Users', then 'Exit'
        $commandTester->setInputs(['0', '5']);
        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithCreateUserOption(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('user-admin');
        $commandTester = new CommandTester($command);

        // Select 'Create User', provide unique email, then 'Exit'
        $email = 'newuser-'.uniqid().'@example.com';
        $commandTester->setInputs(['1', $email, '5']);
        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('User created', $commandTester->getDisplay());
    }

    public function testExecuteWithCreateAdminUserOption(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('user-admin');
        $commandTester = new CommandTester($command);

        // Select 'Create Admin User', provide unique email, then 'Exit'
        $email = 'newadmin-'.uniqid().'@example.com';
        $commandTester->setInputs(['2', $email, '5']);
        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Admin User created', $commandTester->getDisplay());
    }

    public function testExecuteWithEditUserOption(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('user-admin');
        $commandTester = new CommandTester($command);

        // Select 'Edit User', then 'Exit'
        $commandTester->setInputs(['3', '5']);
        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Edit not implemented', $commandTester->getDisplay());
    }

    public function testExecuteWithDeleteUserOption(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('user-admin');
        $commandTester = new CommandTester($command);

        // Select 'Delete User', then 'Exit'
        $commandTester->setInputs(['4', '5']);
        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Delete not implemented', $commandTester->getDisplay());
    }
}
