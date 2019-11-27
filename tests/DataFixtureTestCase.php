<?php

namespace App\Tests;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DataFixtureTestCase extends WebTestCase
{
    /** @var  Application $application */
    protected static $application;

    /** @var  Client $client */
    protected $client;

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


        // $this->client = static::createClient();
        // $this->container = $this->client->getContainer();
        // $this->entityManager = $this->container->get('doctrine.orm.entity_manager');
        // $this->entityManager = static::$container->get('doctrine.orm.entity_manager');

        self::runCommand('doctrine:database:drop', ['--force' =>'--force']);
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:create');
        // self::runCommand('doctrine:fixtures:load --append --no-interaction');

    }

    protected static function runCommand($command, array $arguments = [])
    {
        // $command = sprintf('%s --quiet', $command);

        $options = array_merge(['command' => $command], $arguments);

        $input = new ArrayInput($options);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();

        self::getApplication()->run($input, $output);

        $content = $output->fetch();

        echo $content;

        return $content;
        return self::getApplication()->run(new StringInput($command));
    }

    protected static function getApplication()
    {
        if (null === self::$application) {
            // $client = static::createClient();

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
        // self::runCommand('doctrine:database:drop', ['--force' =>'--force']);

        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
