<?php

namespace App\Tests\EventListener;

use App\EventListener\TwigListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TwigListenerTest extends TestCase
{
    public function testInvokeAddsLocaleGlobal(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('addGlobal')
            ->with('locale', 'es');

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('getLocale')->willReturn('es');

        $listener = new TwigListener($twig, $translator);

        $kernel = $this->createStub(HttpKernelInterface::class);
        $event = new ControllerEvent(
            $kernel,
            static fn() => null,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
        );

        $listener($event);
    }
}
