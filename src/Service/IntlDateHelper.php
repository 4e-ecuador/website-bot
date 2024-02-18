<?php

namespace App\Service;

use DateTime;
use DateTimeZone;
use IntlDateFormatter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

class IntlDateHelper
{
    private readonly DateTimeZone $defaultTimezone;

    private readonly IntlDateFormatter $formatterLong;

    private readonly IntlDateFormatter $formatterShort;

    private readonly string $locale;

    private readonly string $timeZone;

    public function __construct(
        #[Autowire('%env(DEFAULT_TIMEZONE)%')] string $defaultTimeZone,
        TranslatorInterface $translator
    ) {
        $locale = $translator->getLocale();
        $this->defaultTimezone = new DateTimeZone($defaultTimeZone);
        $this->formatterLong = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $defaultTimeZone,
            IntlDateFormatter::GREGORIAN,
            $translator->trans('date.format.long')
        );

        $this->formatterShort = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $defaultTimeZone,
            IntlDateFormatter::GREGORIAN,
            $translator->trans('date.format.short')
        );

        $this->locale = $locale;
        $this->timeZone = $defaultTimeZone;
    }

    public function format(DateTime $date): string
    {
        return (string)$this->formatterLong->format($date);
    }

    public function formatShort(DateTime $date): string
    {
        return (string)$this->formatterShort->format($date);
    }

    public function getDefaultTimezone(): DateTimeZone
    {
        return $this->defaultTimezone;
    }

    public function formatCustom(DateTime $date, string $format): string
    {
        $fmt = new IntlDateFormatter(
            $this->locale, IntlDateFormatter::FULL, IntlDateFormatter::FULL,
            $this->timeZone, IntlDateFormatter::GREGORIAN, $format
        );

        return (string)$fmt->format($date);
    }
}
