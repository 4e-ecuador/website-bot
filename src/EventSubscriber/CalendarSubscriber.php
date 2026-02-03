<?php

namespace App\EventSubscriber;

use App\Repository\IngressEventRepository;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\SetDataEvent;
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
            SetDataEvent::class => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(SetDataEvent $setDataEvent): void
    {
        $start = $setDataEvent->getStart();
        $end = $setDataEvent->getEnd();
        //$filters = $setDataEvent->getFilters();

        /**
         * @var DateTime $next
         */
        $next = clone $start;
        foreach (range(0, 2) as $x) {
            $setDataEvent->addEvent(
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
            $setDataEvent->addEvent(
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
            $setDataEvent->addEvent(
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
