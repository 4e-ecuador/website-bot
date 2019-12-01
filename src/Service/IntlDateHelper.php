<?php

namespace App\Service;

use IntlDateFormatter;

class IntlDateHelper
{
    public function format(\DateTime $date)
    {
        $formatter = new IntlDateFormatter(
            'es',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $_ENV['DEFAULT_TIMEZONE'],
            IntlDateFormatter::GREGORIAN,
            'd \'de\' MMMM \'de\' y'
        );

        return $formatter->format($date);
    }

    public function formatShort(\DateTime $date)
    {
        $formatter = new IntlDateFormatter(
            'es',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $_ENV['DEFAULT_TIMEZONE'],
            IntlDateFormatter::GREGORIAN,
            'd \'de\' MMMM'
        );

        return $formatter->format($date);
    }
}
