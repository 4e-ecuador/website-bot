<?php

namespace App\Service;

use DateTime;
use DateTimeZone;

class DateTimeHelper
{
    private DateTimeZone $timezone;

    public function __construct(string $defaultTimeZone)
    {
        $this->timezone = new DateTimeZone($defaultTimeZone);
    }

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
}
