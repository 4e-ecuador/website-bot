<?php

namespace App\Service;

use App\Entity\Event;
use App\Repository\ChallengeRepository;
use App\Repository\EventRepository;
use DateTime;
use DateTimeZone;
use UnexpectedValueException;

class EventHelper
{
    private DateTimeZone $timezone;
    private EventRepository $eventRepository;
    private ChallengeRepository $challengeRepository;

    public function __construct(EventRepository $eventRepository, ChallengeRepository $challengeRepository)
    {
        $this->timezone = new DateTimeZone('');
        $this->eventRepository = $eventRepository;
        $this->challengeRepository = $challengeRepository;
    }

    public function getNextFS(): DateTime
    {
        $dateNow = new DateTime('now', $this->timezone);
        $fsThisMonth = new DateTime('first saturday of this month', $this->timezone);
        $fsNextMonth = new DateTime('first saturday of next month', $this->timezone);

        return ($dateNow > $fsThisMonth) ? $fsNextMonth : $fsThisMonth;
    }

    public function calculateResults(Event $event, iterable $entries): array
    {
        $previousEntries = [];
        $currentEntries = [];

        foreach ($entries as $entry) {
            $agentName = $entry->getAgent()->getNickname();

            if (false === isset($previousEntries[$agentName])) {
                $previousEntries[$agentName] = $entry;
            } else {
                $currentEntries[$agentName] = $entry;
            }
        }

        $values = [];

        foreach (array_keys($currentEntries) as $agentName) {
            if ('fieldslinks' === $event->getEventType()) {
                $fields = $currentEntries[$agentName]->getMindController()
                    - $previousEntries[$agentName]->getMindController();
                $links = $currentEntries[$agentName]->getConnector()
                    - $previousEntries[$agentName]->getConnector();
                $values[$agentName] = $links ? $fields / $links : 0;
            } else {
                $methodName = 'get'.$event->getEventType();

                if (false
                    === method_exists($currentEntries[$agentName], $methodName)
                ) {
                    throw new UnexpectedValueException(
                        'Unknown event type: '.$event->getEventType()
                    );
                }
                $values[$agentName] = $currentEntries[$agentName]->$methodName()
                    - $previousEntries[$agentName]->$methodName();
            }
        }

        arsort($values);

        return $values;
    }

    public function getEventsInSpan(string $span): ?array
    {
        static $events = [], $pastEvents = [], $currentEvents = [], $futureEvents = [];

        if (!$events) {
            $events = $this->eventRepository->findAll();

            $now = new DateTime('now', $this->timezone);

            foreach ($events as $event) {
                $event->setDateStart(
                    new DateTime(
                        $event->getDateStart()
                            ->format('Y-m-d H:i:s'), $this->timezone
                    )
                );
                $event->setDateEnd(
                    new DateTime(
                        $event->getDateEnd()
                            ->format('Y-m-d H:i:s'), $this->timezone
                    )
                );
                if ($event->getDateStart() > $now) {
                    $futureEvents[] = $event;
                } elseif ($event->getDateEnd() < $now) {
                    $pastEvents[] = $event;
                } else {
                    $currentEvents[] = $event;
                }
            }
        }

        switch ($span) {
            case 'past':
                return $pastEvents;
            case 'present':
                return $currentEvents;
            case 'future':
                return $futureEvents;
            default:
                throw new UnexpectedValueException('Unknown span (must be: past, present or future)');
        }
    }

    public function getChallengesInSpan(string $span): ?array
    {
        static $items = [], $challenges = [];


        if (!$items) {
            $challenges['past'] = [];
            $challenges['present'] = [];
            $challenges['future'] = [];

            $items = $this->challengeRepository->findAll();

            $now = new DateTime('now', $this->timezone);

            foreach ($items as $item) {
                $item->setDateStart(
                    new DateTime(
                        $item->getDateStart()
                            ->format('Y-m-d H:i:s'), $this->timezone
                    )
                );
                $item->setDateEnd(
                    new DateTime(
                        $item->getDateEnd()
                            ->format('Y-m-d H:i:s'), $this->timezone
                    )
                );
                if ($item->getDateStart() > $now) {
                    $challenges['future'][] = $item;
                } elseif ($item->getDateEnd() < $now) {
                    $challenges['past'][] = $item;
                } else {
                    $challenges['present'][] = $item;
                }
            }
        }

        if (array_key_exists($span, $challenges)) {
            return $challenges[$span];
        }

        throw new UnexpectedValueException('Unknown span (must be: past, present or future): '.$span);
    }
}
