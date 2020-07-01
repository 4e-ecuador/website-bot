<?php

namespace App\Service;

use DateTimeZone;
use IntlDateFormatter;
use Symfony\Contracts\Translation\TranslatorInterface;

class IntlDateHelper
{
    private DateTimeZone $defaultTimezone;
    private IntlDateFormatter $formatterLong;
    private IntlDateFormatter $formatterShort;
    private string $locale;
    private string $timeZone;

    public function __construct(string $timeZone, TranslatorInterface $translator)
    {
        $locale = $translator->getLocale();
        $this->defaultTimezone = new \DateTimeZone($timeZone);
        $this->formatterLong = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $timeZone,
            IntlDateFormatter::GREGORIAN,
            'd \'de\' MMMM \'de\' y'
        );

        $this->formatterShort = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $timeZone,
            IntlDateFormatter::GREGORIAN,
            'd \'de\' MMMM'
        );

        $this->locale = $locale;
        $this->timeZone = $timeZone;
    }

    public function format(\DateTime $date)
    {
        return $this->formatterLong->format($date);
    }

    public function formatShort(\DateTime $date)
    {
        return $this->formatterShort->format($date);
    }

    public function getDefaultTimezone(): DateTimeZone
    {
        return $this->defaultTimezone;
    }

    public function formatCustom($date, $format)
    {
        $fmt = new IntlDateFormatter(
            $this->locale, IntlDateFormatter::FULL, IntlDateFormatter::FULL,
            $this->timeZone, IntlDateFormatter::GREGORIAN, $format
        );

        return $fmt->format($date);
    }
}
