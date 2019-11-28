<?php

namespace App\Service;

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
}
