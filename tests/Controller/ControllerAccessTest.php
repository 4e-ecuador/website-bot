<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerAccessTest extends WebTestCase
{
    private $routeLoader;

    private $exceptions = [
        'default'               => [
            'expected' => 200,
        ],
        'app_login'             => [
            'expected' => 200,
        ],
        'agent_add_comment'     => [
            'method' => 'POST',
        ],
        'comment_show'          => [
            'params' => [
                'id' => 33,
            ],
        ],
        'comment_edit'          => [
            'params' => [
                'id' => 33,
            ],
        ],
        'comment_delete'        => [
            'params' => [
                'id' => 33,
            ],
        ],
        'comment_delete_inline' => [
            'method' => 'DELETE',
            'params' => [
                'id' => 33,
            ],
        ],
        'user_show'             => [
            'params' => [
                'id' => 4,
            ],
        ],
        'user_edit'             => [
            'params' => [
                'id' => 4,
            ],
        ],
        'user_delete'           => [
            'params' => [
                'id' => 4,
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->routeLoader = $kernel->getContainer()
            ->get('routing.loader');
    }

    private function loadRoutes($controllerName)
    {
        $routerClass = 'App\Controller\\'.$controllerName;

        return $this->routeLoader->load($routerClass);
    }

    public function testShowPost()
    {
        $controllers = [
            'AccountController',
            'AgentController',
            'AgentStatController',
            'CommentController',
            'DefaultController',
//            'GoogleController',
            'ImportController',
            'SecurityController',
            'StatsController',
            'UserController',
        ];

        foreach ($controllers as $controller) {

            $routes = $this->loadRoutes($controller)->all();
            $client = static::createClient();

            foreach ($routes as $routeName => $route) {

                $method          = 'GET';
                $defaultId       = 8;
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
                $this->assertEquals($defaultExpected, $client->getResponse()->getStatusCode());
            }
        }
    }
}
