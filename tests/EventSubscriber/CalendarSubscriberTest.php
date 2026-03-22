<?php

namespace App\Tests\EventSubscriber;

use App\Entity\IngressEvent;
use App\EventSubscriber\CalendarSubscriber;
use App\Repository\IngressEventRepository;
use CalendarBundle\Event\SetDataEvent;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriberTest extends TestCase
{
    public function testGetSubscribedEventsContainsSetDataEvent(): void
    {
        $subscribed = CalendarSubscriber::getSubscribedEvents();
        self::assertArrayHasKey(SetDataEvent::class, $subscribed);
        self::assertSame('onCalendarSetData', $subscribed[SetDataEvent::class]);
    }

    public function testOnCalendarSetDataAddsFirstSaturdayAndSecondSundayEvents(): void
    {
        $router = $this->createStub(UrlGeneratorInterface::class);
        $router->method('generate')->willReturn('/ingress/event/show/1');

        $ingressEventRepository = $this->createStub(IngressEventRepository::class);
        $ingressEventRepository->method('findBetween')->willReturn([]);

        $subscriber = new CalendarSubscriber($router, $ingressEventRepository);

        $start = new DateTime('2024-01-01');
        $end = new DateTime('2024-03-31');

        $setDataEvent = new SetDataEvent($start, $end, []);
        $subscriber->onCalendarSetData($setDataEvent);

        // Should add 3 months × 2 events (FS + Second Sunday) = 6 events
        self::assertCount(6, $setDataEvent->getEvents());
    }

    public function testOnCalendarSetDataAddsIngressEvents(): void
    {
        $router = $this->createStub(UrlGeneratorInterface::class);
        $router->method('generate')->willReturn('/ingress/event/show/1');

        $ingressEvent = new IngressEvent();
        $ingressEvent->setName('Test FS Event');
        $ingressEvent->setDateStart(new DateTime('2024-01-06'));
        $ingressEvent->setDateEnd(new DateTime('2024-01-06'));

        $ingressEventRepository = $this->createStub(IngressEventRepository::class);
        $ingressEventRepository->method('findBetween')->willReturn([$ingressEvent]);

        $subscriber = new CalendarSubscriber($router, $ingressEventRepository);

        $start = new DateTime('2024-01-01');
        $end = new DateTime('2024-03-31');

        $setDataEvent = new SetDataEvent($start, $end, []);
        $subscriber->onCalendarSetData($setDataEvent);

        // 6 FS/SS events + 1 ingress event = 7
        self::assertCount(7, $setDataEvent->getEvents());
    }
}
