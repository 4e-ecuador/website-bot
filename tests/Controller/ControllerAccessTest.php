<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Tests\FixtureAwareTestCase;
use App\Tests\Fixtures\AgentFixture;
use App\Tests\Fixtures\AgentStatFixture;
use App\Tests\Fixtures\CommentFixture;
use App\Tests\Fixtures\EventFixture;
use App\Tests\Fixtures\HelpFixture;

class ControllerAccessTest extends FixtureAwareTestCase
{
    private $routeLoader;

    private $exceptions
        = [
            'default'               => [
                'expected' => 200,
            ],
            'app_login'             => [
                'expected' => 200,
            ],
            'agent-map'             => [
                'expected' => 200,
            ],
            'map-json'              => [
                'expected' => 200,
            ],
            'agent-info'            => [
                'expected' => 200,
            ],
            'agent_add_comment'     => [
                'method' => 'POST',
            ],
            'agent_lookup'          => [
                'method' => 'POST',
            ],
            'comment_delete_inline' => [
                'method' => 'DELETE',
            ],
        ];

    protected function setUp()
    {
        parent::setUp();
        $kernel = static::bootKernel();

        $this->addFixture(new AgentFixture());
        $this->addFixture(new AgentStatFixture());
        $this->addFixture(new CommentFixture());
        $this->addFixture(new HelpFixture());
        $this->addFixture(new EventFixture());
        $this->executeFixtures();

        $this->agentRepository = $kernel->getContainer()->get('doctrine')
            ->getRepository(Agent::class);

        $this->entityManager = $kernel->getContainer()->get('doctrine')
            ->getManager();

        $this->routeLoader = $kernel->getContainer()->get('routing.loader');
    }

    public function XXXtestGetAgents()
    {
        $agents = $this->entityManager->getRepository(Agent::class)->findAll();
        $this->assertEquals(5, count($agents));
    }

    private function loadRoutes($controllerName)
    {
        $routerClass = 'App\Controller\\'.$controllerName;

        return $this->routeLoader->load($routerClass);
    }

    public function testShowPage()
    {
        $path = __DIR__.'/../../src/Controller';

        foreach (new \DirectoryIterator($path) as $item) {
            if (
                $item->isDot()
                || in_array(
                    $item->getBasename(), ['.gitignore', 'GoogleController.php']
                )
            ) {
                continue;
            }

            $controllerName = basename($item->getBasename(), '.php');

            $routes = $this->loadRoutes($controllerName)->all();
            $client = static::createClient();

            foreach ($routes as $routeName => $route) {
                $method = 'GET';
                $defaultId = 1;
                $defaultExpected = 302;

                if (array_key_exists($routeName, $this->exceptions)) {
                    if (array_key_exists('method', $this->exceptions[$routeName])) {
                        $method = $this->exceptions[$routeName]['method'];
                    }
                    if (array_key_exists('expected', $this->exceptions[$routeName])) {
                        $defaultExpected = $this->exceptions[$routeName]['expected'];
                    }
                    if (array_key_exists('params', $this->exceptions[$routeName])) {
                        $params = $this->exceptions[$routeName]['params'];
                        if (array_key_exists('id', $params)) {
                            $defaultId = $params['id'];
                        }
                    }
                }

                $path = $route->getPath();
                $path = str_replace('{id}', $defaultId, $path);
                $client->request($method, $path);
                $this->assertEquals(
                    $defaultExpected,
                    $client->getResponse()->getStatusCode(),
                    sprintf('failed: %s (%s)', $routeName, $path)
                );
            }
        }
    }
}
