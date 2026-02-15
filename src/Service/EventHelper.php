<?php

namespace App\Service;

use App\Entity\AgentStat;
use App\Entity\Challenge;
use App\Entity\Event;
use App\Repository\ChallengeRepository;
use App\Repository\EventRepository;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use UnexpectedValueException;

class EventHelper
{
    private readonly DateTimeZone $timezone;

    /**
     * @var array<string, array<Event>>|null
     */
    private ?array $eventsBySpan = null;

    /**
     * @var array<string, array<Challenge>>|null
     */
    private ?array $challengesBySpan = null;

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ChallengeRepository $challengeRepository,
        #[Autowire('%env(DEFAULT_TIMEZONE)%')] string $defaultTimeZone
    ) {
        $this->timezone = new DateTimeZone($defaultTimeZone);
    }

    /**
     * @throws Exception
     */
    public function getNextFS(): DateTime
    {
        $dateNow = new DateTime('now', $this->timezone);
        $fsThisMonth = new DateTime(
            'first saturday of this month',
            $this->timezone
        );
        $fsNextMonth = new DateTime(
            'first saturday of next month',
            $this->timezone
        );

        return ($dateNow > $fsThisMonth) ? $fsNextMonth : $fsThisMonth;
    }

    /**
     * @param iterable<AgentStat> $entries
     *
     * @return array<string, int>
     */
    public function calculateResults(Event $event, iterable $entries): array
    {
        $previousEntries = [];
        $currentEntries = [];

        foreach ($entries as $entry) {
            $agentName = $entry->getAgent()?->getNickname();

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
                $values[$agentName] = $links !== 0 ? $fields / $links : 0;
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

    /**
     * @return array<Event>
     * @throws Exception
     */
    public function getEventsInSpan(string $span): ?array
    {
        if ($this->eventsBySpan === null) {
            $this->eventsBySpan = ['past' => [], 'present' => [], 'future' => []];

            $now = new DateTime('now', $this->timezone)
                ->setTime(12, 0);

            foreach ($this->eventRepository->findAll() as $event) {
                $event->setDateStart(
                    new DateTime(
                        $event->getDateStart()
                            ->format('Y-m-d H:i:s'), $this->timezone
                    )->setTime(12, 0)
                );
                $event->setDateEnd(
                    new DateTime(
                        $event->getDateEnd()
                            ->format('Y-m-d H:i:s'), $this->timezone
                    )->setTime(12, 0)
                );
                if ($event->getDateStart() > $now) {
                    $this->eventsBySpan['future'][] = $event;
                } elseif ($event->getDateEnd() < $now) {
                    $this->eventsBySpan['past'][] = $event;
                } else {
                    $this->eventsBySpan['present'][] = $event;
                }
            }
        }

        return match ($span) {
            'past' => $this->eventsBySpan['past'],
            'present' => $this->eventsBySpan['present'],
            'future' => $this->eventsBySpan['future'],
            default => throw new UnexpectedValueException(
                'Unknown span (must be: past, present or future)'
            ),
        };
    }

    /**
     * @return array<Challenge>|null
     * @throws Exception
     */
    public function getChallengesInSpan(string $span): ?array
    {
        if ($this->challengesBySpan === null) {
            $this->challengesBySpan = ['past' => [], 'present' => [], 'future' => []];

            $now = new DateTime('now', $this->timezone)
                ->setTime(12, 0);

            foreach ($this->challengeRepository->findAll() as $item) {
                $item->setDateStart(
                    new DateTime(
                        $item->getDateStart()
                            ->format('Y-m-d H:i:s'), $this->timezone
                    )->setTime(12, 0)
                );
                $item->setDateEnd(
                    new DateTime(
                        $item->getDateEnd()
                            ->format('Y-m-d H:i:s'), $this->timezone
                    )->setTime(12, 0)
                );
                if ($item->getDateStart() > $now) {
                    $this->challengesBySpan['future'][] = $item;
                } elseif ($item->getDateEnd() < $now) {
                    $this->challengesBySpan['past'][] = $item;
                } else {
                    $this->challengesBySpan['present'][] = $item;
                }
            }
        }

        if (array_key_exists($span, $this->challengesBySpan)) {
            return $this->challengesBySpan[$span];
        }

        throw new UnexpectedValueException(
            'Unknown span (must be: past, present or future): '.$span
        );
    }
}
