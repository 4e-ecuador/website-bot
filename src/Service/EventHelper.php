<?php

namespace App\Service;

use App\Entity\Event;
use DateTime;

class EventHelper
{
    /**
     * @var string
     */
    private $defaultTimezone;

    /**
     * @var \DateTimeZone
     */
    private $timezone;

    public function __construct()
    {
        $this->defaultTimezone = $_ENV['DEFAULT_TIMEZONE'];
        $this->timezone = new \DateTimeZone($this->defaultTimezone);
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
                    throw new \UnexpectedValueException(
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
}
