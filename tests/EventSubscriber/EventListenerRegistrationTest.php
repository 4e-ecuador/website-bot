<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\CalendarSubscriber;
use App\EventSubscriber\LoginAttemptSubscriber;
use App\Subscriber\TelegramUpdateSubscriber;
use BoShurik\TelegramBotBundle\Event\UpdateEvent;
use CalendarBundle\Event\SetDataEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class EventListenerRegistrationTest extends KernelTestCase
{
    /**
     * @return iterable<string, array{string, class-string, string, int}>
     */
    public static function listenerProvider(): iterable
    {
        yield 'calendar data' => [SetDataEvent::class, CalendarSubscriber::class, 'onCalendarSetData', 0];
        yield 'login success' => [LoginSuccessEvent::class, LoginAttemptSubscriber::class, 'onLoginSuccess', 0];
        yield 'login failure' => [LoginFailureEvent::class, LoginAttemptSubscriber::class, 'onLoginFailure', 0];
        yield 'telegram check' => [UpdateEvent::class, TelegramUpdateSubscriber::class, 'check', 99];
        yield 'telegram process' => [UpdateEvent::class, TelegramUpdateSubscriber::class, 'processUpdate', 0];
        yield 'telegram log' => [UpdateEvent::class, TelegramUpdateSubscriber::class, 'writeLog', -10];
    }

    /**
     * @param class-string $listenerClass
     */
    #[DataProvider('listenerProvider')]
    public function testListenerIsRegistered(string $event, string $listenerClass, string $method, int $priority): void
    {
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertInstanceOf(EventDispatcherInterface::class, $dispatcher);

        $matching = array_values(array_filter(
            $dispatcher->getListeners($event),
            static fn (mixed $listener): bool => is_array($listener)
                && $listener[0] instanceof $listenerClass
                && $method === $listener[1],
        ));

        self::assertCount(1, $matching, sprintf('%s::%s() is not registered for %s', $listenerClass, $method, $event));
        self::assertSame($priority, $dispatcher->getListenerPriority($event, $matching[0]));
    }
}
