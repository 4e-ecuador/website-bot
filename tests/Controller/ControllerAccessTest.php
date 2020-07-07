<?php

namespace App\Tests\Controller;

use DirectoryIterator;
use Exception;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerAccessTest extends WebTestCase
{
    use RecreateDatabaseTrait;

    private array $exceptions
        = [
            'default'                  => [
                'statusCode' => 200,
            ],
            'app_login'                => [
                'statusCode' => 200,
            ],
            'connect_google_api_token' => [
                'statusCode' => 200,
            ],
        ];

    /**
     * @throws Exception
     */
    public function testRoutes(): void
    {
        $client = static::createClient();
        $routeLoader = static::bootKernel()->getContainer()
            ->get('routing.loader');

        foreach (
            new DirectoryIterator(__DIR__.'/../../src/Controller') as $item
        ) {
            if (
                $item->isDot()
                || $item->isDir()
                || in_array(
                    $item->getBasename(),
                    ['.gitignore', 'GoogleController.php']
                )
            ) {
                continue;
            }

            $routerClass = 'App\Controller\\'.basename(
                    $item->getBasename(),
                    '.php'
                );
            $routes = $routeLoader->load($routerClass)->all();

            $this->processRoutes($routes, $client);
        }
    }

    private function processRoutes(array $routes, KernelBrowser $browser): void
    {
        foreach ($routes as $routeName => $route) {
            $defaultId = 1;
            $expectedStatusCode = 302;
            if (array_key_exists($routeName, $this->exceptions)) {
                if (array_key_exists(
                    'statusCode',
                    $this->exceptions[$routeName]
                )
                ) {
                    $expectedStatusCode = $this->exceptions[$routeName]['statusCode'];
                }
                if (array_key_exists('params', $this->exceptions[$routeName])) {
                    $params = $this->exceptions[$routeName]['params'];
                    if (array_key_exists('id', $params)) {
                        $defaultId = $params['id'];
                    }
                }
            }

            $methods = $route->getMethods() ?: ['GET'];
            $path = str_replace('{id}', $defaultId, $route->getPath());
            foreach ($methods as $method) {
                // echo "Testing: $method - $path".PHP_EOL;
                $browser->request($method, $path);
                $this->assertEquals(
                    $expectedStatusCode,
                    $browser->getResponse()->getStatusCode(),
                    sprintf('failed: %s (%s)', $routeName, $path)
                );
            }
        }
    }
}
