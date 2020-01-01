<?php

namespace App\Tests;

use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class DataFixtureTestCase extends WebTestCase
{
    /** @var  Application $application */
    protected static $application;

    /** @var  EntityManager $entityManager */
    protected $entityManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::runCommand('doctrine:database:drop', ['--force' => '--force']);
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:create');
    }

    protected static function runCommand($command, array $arguments = []): string
    {
        $options = array_merge(['command' => $command], $arguments);

        $input = new ArrayInput($options);

        $output = new BufferedOutput();

        try {
            self::getApplication()->run($input, $output);
            $content = $output->fetch();
        } catch (Exception $e) {
            $content = $e->getMessage();
        }

        echo $content;

        return $content;
    }

    protected static function getApplication(): Application
    {
        if (null === self::$application) {
            self::$application = new Application(self::bootKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
