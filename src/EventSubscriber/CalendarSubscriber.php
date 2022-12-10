<?php

namespace App\EventSubscriber;

use App\Repository\IngressEventRepository;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use DateTime;
use DateTimeZone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly IngressEventRepository $ingressEventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar): void
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();

        /**
         * @var DateTime $next
         */
        $next = clone $start;
        foreach (range(0, 2) as $x) {
            $calendar->addEvent(
                new Event(
                    'First Saturday',
                    new DateTime(
                        'first saturday of '.$next->format('M Y').' 12:00:00'
                    ),
                    options: [
                        'backgroundColor' => 'green',
                        'borderColor'     => 'green',
                    ]
                )
            );
            $calendar->addEvent(
                new Event(
                    'Second Sunday',
                    new DateTime(
                        'second sunday of '.$next->format('M Y').' 12:00:00'
                    ),
                    options: [
                        'backgroundColor' => 'green',
                        'borderColor'     => 'green',
                    ]
                )
            );

            $next->modify('first day of next month');
        }

        $ingressEvents = $this->ingressEventRepository->findBetween(
            $start,
            $end
        );

        foreach ($ingressEvents as $event) {
            $calendar->addEvent(
                new Event(
                    $event->getName(),
                    $event->getDateStart()->setTimezone(
                        new DateTimeZone('UTC')
                    ),
                    $event->getDateEnd()->setTimezone(new DateTimeZone('UTC')),
                    options: [
                        'backgroundColor' => 'red',
                        'borderColor'     => 'red',
                        'url'             => $this->router->generate(
                            'ingress_event_public_show',
                            [
                                'id' => $event->getId(),
                            ]
                        ),
                    ]
                )
            );
        }
    }
}
