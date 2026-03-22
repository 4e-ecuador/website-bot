<?php

namespace App\Tests\Command;

use App\Command\SortLanguageFilesCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SortLanguageFilesCommandTest extends KernelTestCase
{
    public function testExecuteSortsLanguageFiles(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:sortLanguageFiles');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Language files have been sorted', $commandTester->getDisplay());
    }
}
