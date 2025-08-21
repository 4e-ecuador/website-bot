<?php

namespace App\Service;

use DateTime;
use DateTimeZone;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CalendarHelper
{
    private readonly DateTimeZone $timezone;

    public function __construct(
        #[Autowire('%env(DEFAULT_TIMEZONE)%')] string $defaultTimeZone,
    ) {
        $this->timezone = new DateTimeZone($defaultTimeZone);
    }

    /**
     * @return array<string>
     */
    public function getEvents(?DateTime $date = null): array
    {
        $date ?: new DateTime('midnight', $this->timezone);
        new DateTime('midnight', $this->timezone);

        new DateTime(
            'first saturday of this month',
            $this->timezone
        );

        new DateTime(
            'second sunday of this month',
            $this->timezone
        );

        new DateTime(
            'second thursday of this month',
            $this->timezone
        );

        // dump($date, $date2, $fsThisMonth, $ssThisMonth, $sgThisMonth, $ddd);
        // dump($date2, $sgThisMonth, $ddd);

        $events = [];

        return $events;
    }
}
