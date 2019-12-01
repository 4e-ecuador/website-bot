<?php

namespace App\Service;

use DateTimeZone;
use IntlDateFormatter;

class IntlDateHelper
{
    /**
     * @var DateTimeZone
     */
    private $defaultTimezone;

    /**
     * @var IntlDateFormatter
     */
    private $formatterLong;

    /**
     * @var IntlDateFormatter
     */
    private $formatterShort;

    public function __construct()
    {
        $this->defaultTimezone = new \DateTimeZone($_ENV['DEFAULT_TIMEZONE']);
        $this->formatterLong = new IntlDateFormatter(
            'es',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $_ENV['DEFAULT_TIMEZONE'],
            IntlDateFormatter::GREGORIAN,
            'd \'de\' MMMM \'de\' y'
        );

        $this->formatterShort = new IntlDateFormatter(
            'es',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $_ENV['DEFAULT_TIMEZONE'],
            IntlDateFormatter::GREGORIAN,
            'd \'de\' MMMM'
        );
    }

    public function format(\DateTime $date)
    {
        // $formatter = new IntlDateFormatter(
        //     'es',
        //     IntlDateFormatter::FULL,
        //     IntlDateFormatter::FULL,
        //     $_ENV['DEFAULT_TIMEZONE'],
        //     IntlDateFormatter::GREGORIAN,
        //     'd \'de\' MMMM \'de\' y'
        // );

        return $this->formatterLong->format($date);
    }

    public function formatShort(\DateTime $date)
    {
        // $formatter = new IntlDateFormatter(
        //     'es',
        //     IntlDateFormatter::FULL,
        //     IntlDateFormatter::FULL,
        //     $_ENV['DEFAULT_TIMEZONE'],
        //     IntlDateFormatter::GREGORIAN,
        //     'd \'de\' MMMM'
        // );

        return $this->formatterShort->format($date);
    }

    /**
     * @return DateTimeZone
     */
    public function getDefaultTimezone(): DateTimeZone
    {
        return $this->defaultTimezone;
    }
}
