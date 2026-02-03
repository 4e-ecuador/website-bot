<?php

namespace App\Tests\Controller;

use DirectoryIterator;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Route;

class ControllerAccessTest extends WebTestCase
{
    /**
     * @var array<string, array<string, array<string, int>|int>>
     */
    private array $exceptions
        = [
            'default'                   => [
                'statusCode' => 200,
            ],
            'app_login'                 => [
                'statusCode' => ['GET' => 200, 'POST' => 302],
            ],
            'connect_google_api_token'  => [
                'statusCode' => 200,
            ],
            'event_calendar'            => [
                'statusCode' => 200,
            ],
            'ingress_event_public_show' => [
                'statusCode' => 200,
            ],
            'app_privacy'               => [
                'statusCode' => 200,
            ],
        ];

    /**
     * @throws Exception
     */
    public function testRoutes(): void
    {
        $client = static::createClient([], ['HTTPS' => true]);
        $routeLoader = static::bootKernel()->getContainer()
            ->get('routing.loader');

        foreach (
            new DirectoryIterator(__DIR__.'/../../src/Controller') as $item
        ) {
            if ($item->isDot()) {
                continue;
            }

            if ($item->isDir()) {
                continue;
            }

            if (in_array(
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

    /**
     * @param array<Route> $routes
     */
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
                    $params = (array)$this->exceptions[$routeName]['params'];
                    if (array_key_exists('id', $params)) {
                        $defaultId = $params['id'];
                    }
                }
            }

            $methods = $route->getMethods() ?: ['GET'];
            $path = str_replace('{id}', (string)$defaultId, $route->getPath());
            foreach ($methods as $method) {
                $code = is_array($expectedStatusCode)
                    ? $expectedStatusCode[$method] : $expectedStatusCode;
                echo sprintf('Testing: %s - %s (%d)', $method, $path, $code)
                    .PHP_EOL;
                $browser->request($method, $path);
                $this->assertEquals(
                    $code,
                    $browser->getResponse()->getStatusCode(),
                    sprintf(
                        'failed: %s (%s) with method "%s"',
                        $routeName,
                        $path,
                        $method
                    )
                );
            }
        }
    }
}
