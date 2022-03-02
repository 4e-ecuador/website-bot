<?php

namespace App\Service;

use DateTime;
use DateTimeZone;

class CalendarHelper
{
    private readonly DateTimeZone $timezone;

    public function __construct(
        string $defaultTimeZone,
    ) {
        $this->timezone = new DateTimeZone($defaultTimeZone);
    }

    /**
     * @return array<string>
     */
    public function getEvents(DateTime $date = null): array
    {
        $date = $date ?: new DateTime('midnight', $this->timezone);
        $date2 = new DateTime('midnight', $this->timezone);

        $fsThisMonth = new DateTime(
            'first saturday of this month',
            $this->timezone
        );

        $ssThisMonth = new DateTime(
            'second sunday of this month',
            $this->timezone
        );

        $sgThisMonth = new DateTime(
            'second thursday of this month',
            $this->timezone
        );

        $ddd = $date2 == $sgThisMonth;

        // dump($date, $date2, $fsThisMonth, $ssThisMonth, $sgThisMonth, $ddd);
        // dump($date2, $sgThisMonth, $ddd);

        $events = [];

        return $events;
    }
}
