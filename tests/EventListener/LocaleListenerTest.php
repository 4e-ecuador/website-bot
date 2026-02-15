<?php

namespace App\Tests\EventListener;

use App\Entity\Agent;
use App\Entity\User;
use App\EventListener\LocaleListener;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Translation\Translator;

class LocaleListenerTest extends TestCase
{
    private function createEvent(): RequestEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);

        return new RequestEvent(
            $kernel,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
        );
    }

    public function testNoUserDoesNothing(): void
    {
        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn(null);

        $translator = $this->createMock(Translator::class);
        $translator->expects(self::never())->method('setLocale');

        $listener = new LocaleListener($security, $translator);
        $listener($this->createEvent());
    }

    public function testUserWithoutAgentDoesNothing(): void
    {
        $user = new User();

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($user);

        $translator = $this->createMock(Translator::class);
        $translator->expects(self::never())->method('setLocale');

        $listener = new LocaleListener($security, $translator);
        $listener($this->createEvent());
    }

    public function testAgentWithLocale(): void
    {
        $agent = new Agent();
        $agent->setLocale('es');

        $user = new User();
        $user->setAgent($agent);

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($user);

        $translator = $this->createMock(Translator::class);
        $translator->expects(self::once())->method('setLocale')->with('es');

        $listener = new LocaleListener($security, $translator);
        $listener($this->createEvent());
    }

    public function testAgentWithoutLocaleDoesNotSetLocale(): void
    {
        $agent = new Agent();
        $agent->setLocale('');

        $user = new User();
        $user->setAgent($agent);

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($user);

        $translator = $this->createMock(Translator::class);
        $translator->expects(self::never())->method('setLocale');

        $listener = new LocaleListener($security, $translator);
        $listener($this->createEvent());
    }
}
