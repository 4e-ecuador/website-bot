<?php

namespace App\Service;

use DateTime;
use DateTimeZone;
use Exception;

class DateTimeHelper
{
    private readonly DateTimeZone $timezone;

    public function __construct(string $defaultTimeZone)
    {
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
}
