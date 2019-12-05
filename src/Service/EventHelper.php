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

        $methodName = 'get'.$event->getEventType();

        foreach (array_keys($currentEntries) as $agentName) {
            $values[$agentName] = $currentEntries[$agentName]->$methodName()
                - $previousEntries[$agentName]->$methodName();
        }

        arsort($values);

        return $values;
    }
}
