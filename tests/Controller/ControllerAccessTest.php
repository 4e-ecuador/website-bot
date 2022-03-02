<?php

namespace App\Tests\Controller;

use Elkuku\SymfonyUtils\Test\ControllerBaseTest;

class ControllerAccessTest extends ControllerBaseTest
{

    protected string $controllerRoot = __DIR__.'/../../src/Controller';

    /**
     * @var array<int, string>
     */
    protected array $ignoredFiles
        = [
            '.gitignore',
            'GoogleController.php',
        ];

    /**
     * @var array<string, array<string, array<string, int>>>
     */
    protected array $exceptions
        = [
            'default' => [
                'statusCodes' => ['GET' => 200],
            ],
            'app_login'   => [
                'statusCodes' => ['GET' => 200],
            ],
            'event_calendar'   => [
                'statusCodes' => ['GET' => 200],
            ],
            'ingress_event_public_show'   => [
                'statusCodes' => ['GET' => 200],
            ],
        ];

    public function testRoutes(): void
    {
        $this->runTests(static::createClient());
    }
}
